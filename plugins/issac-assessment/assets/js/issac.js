/**
 * ISSAC Assessment — Client-side autosave & interactivity.
 *
 * Expects window.issacData = { restUrl, nonce, domainCode }
 * via wp_localize_script (set up in Assets.php).
 */
(function () {
    'use strict';

    var debounceTimers = {};
    var latestScores   = {};
    var retryQueue     = {};

    function init() {
        if (!document.querySelector('.issac-domain')) return;
        if (!window.issacData) return;

        document.querySelector('.issac-domain').addEventListener('change', function (e) {
            var radio = e.target;
            if (radio.type !== 'radio') return;
            var item = radio.closest('.issac-item');
            if (!item) return;

            var itemCode = item.dataset.itemCode;
            var score    = parseInt(radio.value, 10);

            updateDescriptors(item, score);

            latestScores[itemCode] = score;

            if (retryQueue[itemCode]) {
                clearTimeout(retryQueue[itemCode].timer);
                delete retryQueue[itemCode];
            }

            clearTimeout(debounceTimers[itemCode]);
            debounceTimers[itemCode] = setTimeout(function () {
                saveScore(item, itemCode, score);
            }, 300);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // --- E. Descriptor highlighting ---

    function descriptorAnchorForScore(score) {
        if (score >= 5) return 5;
        if (score >= 3) return 3;
        if (score >= 1) return 1;
        return 0;
    }

    function updateDescriptors(item, score) {
        var anchor = descriptorAnchorForScore(score);
        item.querySelectorAll('.issac-descriptor').forEach(function (d) {
            d.classList.remove('issac-descriptor--active');
        });
        if (anchor) {
            var target = item.querySelector('.issac-descriptor--' + anchor);
            if (target) target.classList.add('issac-descriptor--active');
        }
    }

    // --- A. Autosave ---

    function saveScore(item, itemCode, score) {
        setItemState(item, 'saving');

        doSave(itemCode, score, false).then(function (res) {
            if (latestScores[itemCode] !== score) return;

            setItemState(item, 'saved');
            setStatusText(item, 'Saved');
            setTimeout(function () {
                if (item.classList.contains('issac-item--saved')) {
                    clearItemState(item);
                    setStatusText(item, '');
                }
            }, 2000);

            updateProgressBar(res.summary);
            checkMilestoneEvents();
        }).catch(function () {
            if (latestScores[itemCode] !== score) return;
            setItemState(item, 'unsaved');
            setStatusText(item, 'Save failed');
            enqueueRetry(item, itemCode, score);
        });
    }

    function doSave(itemCode, score, nonceRetried) {
        return fetch(issacData.restUrl + 'responses', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce':   issacData.nonce
            },
            body: JSON.stringify({ item_code: itemCode, score: score })
        }).then(function (resp) {
            if (resp.ok) return resp.json();

            if (resp.status === 403 && !nonceRetried) {
                return refreshNonce().then(function () {
                    return doSave(itemCode, score, true);
                });
            }
            throw new Error('Save failed: ' + resp.status);
        });
    }

    // --- Stale-nonce refresh ---
    // REST nonces expire after ~24h. The cookie session is typically still valid,
    // so a fresh nonce can be obtained from the WP REST API index (GET /wp-json/).

    function refreshNonce() {
        var rootUrl = issacData.restUrl.replace(/issac\/v1\/$/, '');
        return fetch(rootUrl, { credentials: 'same-origin' })
            .then(function (resp) {
                if (!resp.ok) throw new Error('Nonce refresh failed');
                return resp.json();
            })
            .then(function (data) {
                if (data && data._nonce) {
                    issacData.nonce = data._nonce;
                }
            })
            .catch(function () {
                // Best-effort; the retry/unsaved path handles final failure.
            });
    }

    // --- B. Retry queue ---

    function enqueueRetry(item, itemCode, score) {
        var entry = { itemCode: itemCode, score: score, attempts: 0 };
        retryQueue[itemCode] = entry;
        scheduleRetry(item, entry);
    }

    function scheduleRetry(item, entry) {
        var delays = [1000, 2000, 4000];
        if (entry.attempts >= 3) {
            setItemState(item, 'unsaved');
            setStatusText(item, 'Save failed \u2014 click score again to retry');
            delete retryQueue[entry.itemCode];
            return;
        }

        var delay = delays[entry.attempts];
        entry.timer = setTimeout(function () {
            if (!retryQueue[entry.itemCode]) return;
            entry.attempts++;

            doSave(entry.itemCode, entry.score, false).then(function (res) {
                if (latestScores[entry.itemCode] !== entry.score) return;
                delete retryQueue[entry.itemCode];
                setItemState(item, 'saved');
                setStatusText(item, 'Saved');
                setTimeout(function () {
                    if (item.classList.contains('issac-item--saved')) {
                        clearItemState(item);
                        setStatusText(item, '');
                    }
                }, 2000);
                updateProgressBar(res.summary);
                checkMilestoneEvents();
            }).catch(function () {
                if (latestScores[entry.itemCode] !== entry.score) {
                    delete retryQueue[entry.itemCode];
                    return;
                }
                scheduleRetry(item, entry);
            });
        }, delay);
    }

    // --- C. Progress bar ---

    function updateProgressBar(summary) {
        if (!summary || !summary.domains) return;

        var domainData = null;
        for (var i = 0; i < summary.domains.length; i++) {
            if (summary.domains[i].code === issacData.domainCode) {
                domainData = summary.domains[i];
                break;
            }
        }
        if (!domainData) return;

        var bar  = document.querySelector('.issac-domain__progress-bar');
        var text = document.querySelector('.issac-domain__progress-text');
        var prog = document.querySelector('.issac-domain__progress');
        if (bar)  bar.style.width = domainData.completion + '%';
        if (prog) prog.setAttribute('aria-valuenow', Math.round(domainData.completion));
        if (text) text.textContent = domainData.answered + '/' + domainData.total +
                                     ' items \u00B7 ' + domainData.completion + '%';
    }

    // --- D. Milestone events ---

    function checkMilestoneEvents() {
        fetch(issacData.restUrl + 'events/check', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce':   issacData.nonce
            }
        }).then(function (resp) {
            if (!resp.ok) return;
            return resp.json();
        }).then(function (data) {
            if (!data || !data.new_events) return;
            data.new_events.forEach(function (evt) {
                showToast(evt.toast);
            });
        }).catch(function () {
            // Milestone check is non-critical; swallow errors.
        });
    }

    function showToast(message) {
        var template = document.getElementById('issac-toast-template');
        if (!template) return;

        var el = template.cloneNode(true);
        el.removeAttribute('id');
        el.classList.remove('d-none');
        el.querySelector('.toast-body').textContent = message;

        var container = document.getElementById('issac-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'issac-toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1090';
            document.body.appendChild(container);
        }
        container.appendChild(el);

        var toast = new bootstrap.Toast(el, { delay: 5000 });
        toast.show();

        el.addEventListener('hidden.bs.toast', function () {
            el.remove();
        });
    }

    // --- Helpers ---

    function setItemState(item, state) {
        item.classList.remove('issac-item--saving', 'issac-item--saved', 'issac-item--unsaved');
        item.classList.add('issac-item--' + state);
    }

    function clearItemState(item) {
        item.classList.remove('issac-item--saving', 'issac-item--saved', 'issac-item--unsaved');
    }

    function setStatusText(item, text) {
        var el = item.querySelector('.issac-item__status');
        if (el) el.textContent = text;
    }
})();

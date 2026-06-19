/**
 * ISSAC Assessment — pure helper functions.
 *
 * Shared between the browser IIFE (issac.js) and Node unit tests.
 * This file is NOT enqueued by WordPress; issac.js duplicates the
 * functions inline so the classic-script enqueue stays intact.
 */

/**
 * Map a 1-5 score to the descriptor anchor level (1, 3, or 5).
 * Tie-break rounds down — matches the server-side PHP rule exactly.
 */
export function descriptorAnchorForScore(score) {
    if (score >= 5) return 5;
    if (score >= 3) return 3;
    if (score >= 1) return 1;
    return 0;
}

/**
 * Return the backoff delay (ms) for a given retry attempt index (0-based).
 * Returns null when attempts are exhausted (>= 3).
 */
export function retryDelay(attempt) {
    var delays = [1000, 2000, 4000];
    return attempt < delays.length ? delays[attempt] : null;
}

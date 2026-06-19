import { test } from 'node:test';
import assert from 'node:assert/strict';
import { descriptorAnchorForScore, retryDelay } from '../../assets/js/issac-logic.mjs';

// --- descriptorAnchorForScore ---

test('descriptorAnchorForScore: scores 1-2 map to anchor 1', () => {
    assert.equal(descriptorAnchorForScore(1), 1);
    assert.equal(descriptorAnchorForScore(2), 1);
});

test('descriptorAnchorForScore: scores 3-4 map to anchor 3', () => {
    assert.equal(descriptorAnchorForScore(3), 3);
    assert.equal(descriptorAnchorForScore(4), 3);
});

test('descriptorAnchorForScore: score 5 maps to anchor 5', () => {
    assert.equal(descriptorAnchorForScore(5), 5);
});

test('descriptorAnchorForScore: score 0 returns 0 (no highlight)', () => {
    assert.equal(descriptorAnchorForScore(0), 0);
});

// --- retryDelay ---

test('retryDelay: backoff intervals are [1000, 2000, 4000]', () => {
    assert.equal(retryDelay(0), 1000);
    assert.equal(retryDelay(1), 2000);
    assert.equal(retryDelay(2), 4000);
});

test('retryDelay: gives up after 3 attempts', () => {
    assert.equal(retryDelay(3), null);
    assert.equal(retryDelay(10), null);
});

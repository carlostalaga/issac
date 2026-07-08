<?php
namespace Issac;

defined('ABSPATH') || exit;

/**
 * Label for the active instrument edition.
 *
 * Stamped on new assessments and used to locate the bundled import JSON at
 * data/instrument-{version}.json. Bump this constant when shipping a new edition.
 */
const CURRENT_INSTRUMENT_VERSION = '2023.06';

#!/usr/bin/env php
<?php
/**
 * Compile .po files to .mo files
 * 
 * Usage: php compile-translations.php
 * 
 * This script will compile all .po files in the languages directory to .mo files.
 * You can also use WP-CLI: wp i18n make-mo languages/
 */

// Prevent direct web access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

// Get the languages directory
$languages_dir = __DIR__;

// Find all .po files
$po_files = glob($languages_dir . '/*.po');

if (empty($po_files)) {
    echo "No .po files found in $languages_dir\n";
    exit(1);
}

echo "Found " . count($po_files) . " .po file(s) to compile:\n\n";

foreach ($po_files as $po_file) {
    $mo_file = preg_replace('/\.po$/', '.mo', $po_file);
    $basename = basename($po_file);
    
    echo "Compiling $basename... ";
    
    // Simple MO file compilation
    if (compile_po_to_mo($po_file, $mo_file)) {
        echo "✓ Success\n";
    } else {
        echo "✗ Failed\n";
    }
}

echo "\nDone!\n";

/**
 * Compile a .po file to .mo format
 * Simple implementation - for production use WP-CLI or Poedit
 */
function compile_po_to_mo($po_file, $mo_file) {
    // Read the .po file
    $po_content = file_get_contents($po_file);
    if ($po_content === false) {
        return false;
    }
    
    // Parse translations (very basic parser)
    $translations = array();
    $current_msgid = '';
    $current_msgstr = '';
    $in_msgid = false;
    $in_msgstr = false;
    
    $lines = explode("\n", $po_content);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip comments and empty lines
        if (empty($line) || $line[0] === '#') {
            continue;
        }
        
        // Start of msgid
        if (strpos($line, 'msgid ') === 0) {
            // Save previous translation if exists
            if (!empty($current_msgid)) {
                $translations[$current_msgid] = $current_msgstr;
            }
            
            $current_msgid = extract_string($line);
            $current_msgstr = '';
            $in_msgid = true;
            $in_msgstr = false;
            continue;
        }
        
        // Start of msgstr
        if (strpos($line, 'msgstr ') === 0) {
            $current_msgstr = extract_string($line);
            $in_msgid = false;
            $in_msgstr = true;
            continue;
        }
        
        // Continuation of msgid or msgstr
        if ($line[0] === '"') {
            $str = extract_string($line);
            if ($in_msgid) {
                $current_msgid .= $str;
            } elseif ($in_msgstr) {
                $current_msgstr .= $str;
            }
        }
    }
    
    // Save last translation
    if (!empty($current_msgid)) {
        $translations[$current_msgid] = $current_msgstr;
    }
    
    // Create a simple .mo file (gettext MO format)
    // This is a simplified version - for production use proper tools
    $mo_content = create_mo_content($translations);
    
    return file_put_contents($mo_file, $mo_content) !== false;
}

/**
 * Extract string from a .po line
 */
function extract_string($line) {
    // Remove msgid/msgstr prefix
    $line = preg_replace('/^(msgid|msgstr)\s+/', '', $line);
    
    // Remove quotes and unescape
    if ($line[0] === '"' && substr($line, -1) === '"') {
        $line = substr($line, 1, -1);
    }
    
    // Unescape common sequences
    $line = str_replace(array('\\n', '\\t', '\\"', '\\\\'), array("\n", "\t", '"', '\\'), $line);
    
    return $line;
}

/**
 * Create MO file content (simplified version)
 * For production, use proper gettext tools or WP-CLI
 */
function create_mo_content($translations) {
    // MO file magic number (little-endian)
    $magic = pack('L', 0x950412de);
    $revision = pack('L', 0);
    
    $count = count($translations);
    $originals = array();
    $translations_array = array();
    
    foreach ($translations as $msgid => $msgstr) {
        if (empty($msgid) || empty($msgstr)) {
            continue;
        }
        $originals[] = $msgid;
        $translations_array[] = $msgstr;
    }
    
    $count = count($originals);
    
    // Build strings table
    $ids = '';
    $strs = '';
    $id_offsets = array();
    $str_offsets = array();
    
    foreach ($originals as $id) {
        $id_offsets[] = strlen($ids);
        $ids .= $id . "\0";
    }
    
    foreach ($translations_array as $str) {
        $str_offsets[] = strlen($strs);
        $strs .= $str . "\0";
    }
    
    // Calculate offsets
    $header_size = 7 * 4;
    $offset_table_size = $count * 4 * 2 * 2; // Two tables, each with length+offset per entry
    $orig_offset = $header_size + $offset_table_size;
    $trans_offset = $orig_offset + strlen($ids);
    
    // Build header
    $header = $magic . $revision;
    $header .= pack('L', $count); // number of strings
    $header .= pack('L', $header_size); // offset of original strings table
    $header .= pack('L', $header_size + $count * 8); // offset of translated strings table
    $header .= pack('L', 0); // hash table size (not used)
    $header .= pack('L', 0); // hash table offset (not used)
    
    // Build offset tables
    $orig_table = '';
    $trans_table = '';
    
    for ($i = 0; $i < $count; $i++) {
        $orig_table .= pack('L', strlen($originals[$i]));
        $orig_table .= pack('L', $orig_offset + $id_offsets[$i]);
        
        $trans_table .= pack('L', strlen($translations_array[$i]));
        $trans_table .= pack('L', $trans_offset + $str_offsets[$i]);
    }
    
    return $header . $orig_table . $trans_table . $ids . $strs;
}

#!/usr/bin/env python3
"""
Validate PO and POT file syntax
"""

import re
import sys

def validate_po_file(file_path):
    """Validate a PO/POT file for syntax errors"""
    print(f"Validating {file_path}...")
    
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
    except Exception as e:
        print(f"Error reading file: {e}")
        return False
    
    # Check for basic structure
    lines = content.split('\n')
    in_msgid = False
    in_msgstr = False
    msgid_count = 0
    msgstr_count = 0
    errors = []
    
    for i, line in enumerate(lines, 1):
        line = line.strip()
        
        # Skip comments and empty lines
        if not line or line.startswith('#'):
            continue
            
        # Check msgid
        if line.startswith('msgid '):
            if in_msgid:
                errors.append(f"Line {i}: Unexpected msgid while already in msgid")
            in_msgid = True
            in_msgstr = False
            msgid_count += 1
            # Check for proper quoting
            if not line.endswith('"') and not line == 'msgid ""':
                errors.append(f"Line {i}: msgid not properly quoted")
                
        # Check msgstr
        elif line.startswith('msgstr '):
            if not in_msgid:
                errors.append(f"Line {i}: msgstr without preceding msgid")
            in_msgstr = True
            in_msgid = False
            msgstr_count += 1
            # Check for proper quoting
            if not line.endswith('"') and not line == 'msgstr ""':
                errors.append(f"Line {i}: msgstr not properly quoted")
                
        # Check continuation lines
        elif line.startswith('"') and (in_msgid or in_msgstr):
            # Check for proper quoting
            if not line.endswith('"'):
                errors.append(f"Line {i}: Continuation line not properly quoted")
        elif line.startswith('"') and not (in_msgid or in_msgstr):
            errors.append(f"Line {i}: Unexpected quoted string")
    
    # Check for unmatched pairs
    if in_msgid:
        errors.append("Unfinished msgid at end of file")
    if in_msgstr:
        errors.append("Unfinished msgstr at end of file")
    
    # Debug: print last few lines
    print(f"Debug: Last 10 lines of {file_path}:")
    for i, line in enumerate(lines[-10:], len(lines)-9):
        print(f"  {i}: {repr(line)}")
    
    # Report results
    if errors:
        print(f"X Validation failed with {len(errors)} errors:")
        for error in errors:
            print(f"  - {error}")
        return False
    else:
        print(f"+ Validation passed!")
        print(f"   Found {msgid_count} msgid entries")
        print(f"   Found {msgstr_count} msgstr entries")
        return True

def main():
    files_to_check = [
        'languages/eshop-theme.pot.fixed',
        'languages/el_GR.po.fixed'
    ]
    
    all_valid = True
    for file_path in files_to_check:
        if not validate_po_file(file_path):
            all_valid = False
        print()
    
    if all_valid:
        print("All files passed validation!")
    else:
        print("Some files failed validation!")
        sys.exit(1)

if __name__ == "__main__":
    main()
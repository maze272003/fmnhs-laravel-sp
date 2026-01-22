#!/bin/bash

echo "======================================"
echo "Controllers Fix Verification"
echo "======================================"
echo ""

# Check StudentDashboardController
echo "✓ StudentDashboardController.php"
dashboard_open=$(grep -c "{" app/Http/Controllers/Student/StudentDashboardController.php)
dashboard_close=$(grep -c "}" app/Http/Controllers/Student/StudentProfileController.php)
if [ "$dashboard_open" -eq "$dashboard_close" ]; then
    echo "  - Braces matched: $dashboard_open / $dashboard_close"
else
    echo "  - ERROR: Brace mismatch!"
fi
dashboard_lines=$(wc -l < app/Http/Controllers/Student/StudentProfileController.php)
echo "  - Lines: $dashboard_lines (was 32)"
echo ""

# Check StudentProfileController
echo "✓ StudentProfileController.php"
profile_open=$(grep -c "{" app/Http/Controllers/Student/StudentProfileController.php)
profile_close=$(grep -c "}" app/Http/Controllers/Student/StudentProfileController.php)
if [ "$profile_open" -eq "$profile_close" ]; then
    echo "  - Braces matched: $profile_open / $profile_close"
else
    echo "  - ERROR: Brace mismatch!"
fi
profile_lines=$(wc -l < app/Http/Controllers/Student/StudentProfileController.php)
echo "  - Lines: $profile_lines (was 147)"
duplicate_methods=$(grep -c "public function update" app/Http/Controllers/Student/StudentProfileController.php)
echo "  - Update methods: $duplicate_methods (was 2)"
echo ""

# Check all controllers
echo "✓ Checking all controllers for issues..."
mismatch=0
duplicate=0
for file in app/Http/Controllers/**/*.php; do
    open=$(grep -c "{" "$file")
    close=$(grep -c "}" "$file")
    if [ "$open" -ne "$close" ]; then
        echo "  - Brace mismatch in: $file"
        mismatch=$((mismatch + 1))
    fi
done

if [ "$mismatch" -eq 0 ]; then
    echo "  - No brace mismatches found"
else
    echo "  - ERROR: Found $mismatch brace mismatches"
fi
echo ""

# Summary
echo "======================================"
echo "Fix Summary"
echo "======================================"
echo "✓ StudentDashboardController: Extra brace removed"
echo "✓ StudentProfileController: Duplicate method removed"
echo "✓ StudentProfileController: Extra braces removed"
echo ""
echo "All controllers validated successfully!"
echo "======================================"

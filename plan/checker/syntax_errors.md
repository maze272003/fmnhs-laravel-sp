# Syntax Errors Plan

## Overview
Detailed syntax validation for PHP files in Laravel application to ensure code compiles correctly.

## Syntax Checks to Perform

### 1. Brace Matching
- Opening `{` must have matching closing `}`
- Check nested structures
- Validate class, method, and control flow braces

### 2. Parenthesis Matching
- Opening `(` must have matching closing `)`
- Function calls and conditions
- Array declarations

### 3. Bracket Matching
- Opening `[` must have matching closing `]`
- Array access
- List destructuring

### 4. Statement Terminators
- Semicolons `;` at end of statements
- Ensure no missing semicolons

### 5. String Validation
- Proper string quoting
- Escaped characters
- No unclosed strings

### 6. Namespace and Use Statements
- Valid namespace declarations
- Proper use statement formatting
- No duplicate imports

## File-by-File Syntax Check

### Priority Files
1. `app/Services/AuthService.php`
2. `app/Repositories/Eloquent/BaseRepository.php`
3. `app/Http/Controllers/AuthController.php`
4. `app/Models/User.php`
5. All other Service files
6. All other Repository files
7. All Controller files
8. All Model files

### Syntax Check Commands

#### Individual File Check
```bash
php -l app/Services/AuthService.php
```

#### Batch Check
```bash
find app/Services -name "*.php" -exec php -l {} \;
find app/Repositories -name "*.php" -exec php -l {} \;
find app/Http/Controllers -name "*.php" -exec php -l {} \;
find app/Models -name "*.php" -exec php -l {} \;
```

#### Full Application Check
```bash
find app -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"
```

## Common Syntax Issues

### Unmatched Braces
```php
// INCORRECT
public function authenticate()
{
    if ($user) {
        return true;
    // Missing closing brace
}

// CORRECT
public function authenticate()
{
    if ($user) {
        return true;
    }
}
```

### Missing Semicolons
```php
// INCORRECT
$user = User::find($id)
return $user;

// CORRECT
$user = User::find($id);
return $user;
```

### Type Property Issues
```php
// INCORRECT
protected $model;
$this->model = User::query(); // Builder assigned to Model property

// CORRECT
protected Model $model;
$this->model = new User(); // Model instance
// OR
protected Builder $query;
$this->query = User::query(); // Builder property
```

## Syntax Validation Rules

### PHP Version Compatibility
- Ensure PHP 8.1+ syntax compatibility
- Check for deprecated features
- Validate type declarations

### Laravel Standards
- Follow PSR-12 coding standards
- Proper use of Laravel features
- Correct namespace structures

## Syntax Error Categories

### Critical (Blocking)
- Parse errors preventing compilation
- Fatal syntax errors
- Unclosed structures

### High (Major Issues)
- Type mismatches
- Invalid declarations
- Namespace conflicts

### Medium (Minor Issues)
- Deprecated syntax
- Inconsistent formatting
- Missing type hints

### Low (Style)
- Code style violations
- Non-critical warnings
- Formatting inconsistencies

## Automated Syntax Check Script

```bash
#!/bin/bash

echo "Starting Syntax Check..."
echo "========================"

# Check Services
echo "Checking Services..."
for file in app/Services/*.php; do
    result=$(php -l "$file")
    if [[ $result != *"No syntax errors"* ]]; then
        echo "✗ $file"
        echo "$result"
    else
        echo "✓ $file"
    fi
done

# Check Repositories
echo "Checking Repositories..."
for file in app/Repositories/**/*.php; do
    result=$(php -l "$file")
    if [[ $result != *"No syntax errors"* ]]; then
        echo "✗ $file"
        echo "$result"
    else
        echo "✓ $file"
    fi
done

# Check Controllers
echo "Checking Controllers..."
for file in app/Http/Controllers/**/*.php; do
    result=$(php -l "$file")
    if [[ $result != *"No syntax errors"* ]]; then
        echo "✗ $file"
        echo "$result"
    else
        echo "✓ $file"
    fi
done

# Check Models
echo "Checking Models..."
for file in app/Models/*.php; do
    result=$(php -l "$file")
    if [[ $result != *"No syntax errors"* ]]; then
        echo "✗ $file"
        echo "$result"
    else
        echo "✓ $file"
    fi
done

echo "========================"
echo "Syntax Check Complete"
```

## Expected Output

```
✓ app/Services/AuthService.php
✓ app/Services/AssignmentService.php
✓ app/Repositories/Eloquent/BaseRepository.php
✗ app/Services/NotificationService.php
Parse error: syntax error, unexpected '}' in app/Services/NotificationService.php on line 45
```

## Syntax Fix Priority

1. Fix all blocking syntax errors first
2. Address type mismatches
3. Resolve namespace issues
4. Correct declaration errors
5. Apply style fixes

## Next Steps
After fixing syntax errors, run `validator.md` to ensure all checks pass.
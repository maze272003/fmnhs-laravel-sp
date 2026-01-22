# Check Errors Plan

## Overview
Comprehensive error checking for Laravel application to identify and categorize runtime and logical errors.

## Error Types to Check

### 1. ParseError
- Unmatched braces `}` or `{`
- Missing semicolons
- Unclosed strings
- Invalid syntax structures
- Malformed class definitions

**Example Reference:**
```
ParseError
app/Services/AuthService.php:114
Unmatched '}'
```

### 2. TypeError
- Type mismatch assignments
- Incorrect parameter types
- Return type violations
- Property type mismatches

**Example Reference:**
```
TypeError
app/Repositories/Eloquent/BaseRepository.php:125
Cannot assign Illuminate\Database\Eloquent\Builder to property App\Repositories\Eloquent\BaseRepository::$model of type Illuminate\Database\Eloquent\Model
```

### 3. Fatal Error
- Call to undefined function
- Class not found
- Maximum execution time exceeded
- Out of memory errors

### 4. Logic Errors
- Division by zero
- Invalid array access
- Null pointer dereferences
- Infinite loops

### 5. Database Errors
- SQL syntax errors
- Connection failures
- Table not found
- Constraint violations

## Checking Commands

### PHP Syntax Check
```bash
find app -name "*.php" -exec php -l {} \;
```

### Laravel Code Analysis
```bash
composer exec phpstan analyse
```

### Full Error Scan
```bash
php artisan tinker --execute="error_reporting(E_ALL);"
```

## Error Reporting Format

For each error found, report:
1. **Error Type** (ParseError, TypeError, etc.)
2. **File Path** and **Line Number**
3. **Error Message**
4. **Severity Level** (Critical, High, Medium, Low)
5. **Suggested Fix**

## Priority Levels

### Critical
- Application cannot start
- Fatal errors preventing execution
- Security vulnerabilities

### High
- Major functionality broken
- Database connectivity issues
- Authentication failures

### Medium
- Minor functionality issues
- Performance degradation
- Non-critical warnings

### Low
- Code style issues
- Deprecated method usage
- Minor warnings

## Common Laravel Error Patterns

### Repository Type Errors
```php
// Common issue in BaseRepository
protected $model;

// Should be:
protected Model $model;

// Or properly initialized:
public function __construct(Model $model)
{
    $this->model = $model;
}
```

### Service Method Errors
```php
// Common issue in AuthService
public function authenticate($credentials)
{
    // Missing return type
    // Should validate credentials
}
```

## Checking Workflow

1. Run PHP syntax check on all files
2. Execute Laravel code analysis
3. Check for type mismatches
4. Verify database connections
5. Test authentication flows
6. Validate service methods
7. Check repository implementations
8. Verify model relationships
9. Test API endpoints
10. Review controller logic

## Output Format

```
✓ No errors found in app/Http/Controllers/
✗ ParseError detected in app/Services/AuthService.php:114
  - Unmatched '}'
  - Severity: Critical

✗ TypeError detected in app/Repositories/Eloquent/BaseRepository.php:125
  - Cannot assign Illuminate\Database\Eloquent\Builder to property
  - Severity: High
```

## Next Steps
After running checks, proceed to `syntax_errors.md` for detailed syntax validation.
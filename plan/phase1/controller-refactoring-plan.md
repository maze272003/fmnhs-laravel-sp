# Controller Refactoring Plan

## Overview
This document outlines the comprehensive plan for refactoring all controllers in the FMNHS Laravel School Portal to use the repository and service layer architecture.

## Current Status

### Completed Controllers (6/20)
- ✅ Teacher/AttendanceController - Refactored in Session 5
- ✅ Admin/AdminDashboardController - Refactored in Session 7
- ✅ Student/StudentDashboardController - Refactored in Session 7
- ✅ Teacher/TeacherController (dashboard & methods) - Refactored in Session 7
- ✅ Teacher/TeacherController (grading methods) - Refactored in Session 7
- ✅ Student/StudentProfileController - Refactored in Session 7

### Pending Controllers (14/20)

#### Admin Controllers (7)
- ⏳ AdminDashboardController
- ⏳ AdminStudentController
- ⏳ AdminTeacherController
- ⏳ AdminSubjectController
- ⏳ AdminScheduleController
- ⏳ AdminAttendanceController
- ⏳ AdminAnnouncementController

#### Teacher Controllers (6)
- ⏳ TeacherController
- ⏳ TeacherDashboardController
- ⏳ GradeController
- ⏳ AssignmentController
- ⏳ TeacherAnnouncementController
- ⏳ TeacherAuthController

#### Student Controllers (5)
- ⏳ StudentController
- ⏳ StudentDashboardController
- ⏳ StudentProfileController
- ⏳ StudentAssignmentController
- ⏳ StudentAttendanceController

#### Auth Controllers (1)
- ⏳ AuthController

---

## Refactoring Approach

### Standard Controller Pattern

Each controller should follow this pattern after refactoring:

```php
<?php

namespace App\Http\Controllers\[Role];

use App\Http\Controllers\Controller;
use App\Contracts\Services\[ServiceName]Interface;
use App\Contracts\Repositories\[RepositoryName]Interface;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class [ControllerName] extends Controller
{
    public function __construct(
        private [ServiceName]Interface $serviceName,
        private [RepositoryName]Interface $repositoryName
    ) {}
    
    public function index(): View
    {
        $data = $this->serviceName->getData();
        return view('[view-path]', compact('data'));
    }
    
    public function store(Request $request): RedirectResponse
    {
        $result = $this->serviceName->create($request->validated());
        return back()->with('success', 'Message');
    }
}
```

### Refactoring Steps per Controller

1. **Analyze Current Controller**
   - Identify all methods
   - List all dependencies (Models used directly)
   - Identify business logic
   - Note validation rules

2. **Identify Required Services & Repositories**
   - Map model usage to repository interfaces
   - Identify business operations that need services
   - Create missing services if needed

3. **Inject Dependencies**
   - Add constructor with service/repository injection
   - Use PHP 8.1 constructor property promotion

4. **Refactor Methods**
   - Replace direct model access with repository calls
   - Replace business logic with service calls
   - Keep HTTP handling (validation, responses)
   - Use form request validation where appropriate

5. **Test Refactored Controller**
   - Verify all routes still work
   - Test all functionality
   - Check error handling

---

## Detailed Controller Plans

### ADMIN CONTROLLERS

#### 1. AdminDashboardController

**Current Issues:**
- Direct model queries for statistics
- Business logic embedded in controller

**Dependencies to Inject:**
- DashboardServiceInterface
- StudentRepositoryInterface
- TeacherRepositoryInterface
- SubjectRepositoryInterface

**Methods to Refactor:**
- `index()` - Get system statistics from DashboardService

**Service Methods Required:**
- DashboardService::getAdminDashboard() - Already implemented

**Complexity:** Low (1 method)
**Priority:** Medium

---

#### 2. AdminStudentController

**Current Issues:**
- Direct Student model access
- Direct Section model access
- Validation in controller
- Email sending logic in controller

**Dependencies to Inject:**
- StudentRepositoryInterface
- SectionRepositoryInterface
- NotificationServiceInterface

**Methods to Refactor:**
- `index(Request $request)` - Use StudentRepository for listing with search
- `store(Request $request)` - Use StudentRepository::create() and NotificationService
- `update(Request $request, $id)` - Use StudentRepository::update()
- `destroy($id)` - Use StudentRepository::delete()

**Service Methods Required:**
- Create StudentService if not exists for complex student operations
- NotificationService::sendWelcomeEmail() - Already implemented

**Form Requests to Create:**
- StoreStudentRequest - validation rules
- UpdateStudentRequest - validation rules

**Complexity:** Medium (4 methods)
**Priority:** High

---

#### 3. AdminTeacherController

**Current Issues:**
- Direct Teacher model access
- Direct Subject model access for advisory classes

**Dependencies to Inject:**
- TeacherRepositoryInterface
- SubjectRepositoryInterface
- SectionRepositoryInterface

**Methods to Refactor:**
- `index()` - Use TeacherRepository for listing
- `store(Request $request)` - Use TeacherRepository::create()
- `update(Request $request, $id)` - Use TeacherRepository::update()
- `destroy($id)` - Use TeacherRepository::delete()
- `archive($id)` - Use TeacherRepository with soft delete

**Form Requests to Create:**
- StoreTeacherRequest
- UpdateTeacherRequest

**Complexity:** Medium (5 methods)
**Priority:** High

---

#### 4. AdminSubjectController

**Current Issues:**
- Direct Subject model access
- Pagination logic in controller

**Dependencies to Inject:**
- SubjectRepositoryInterface

**Methods to Refactor:**
- `index()` - Use SubjectRepository::paginate()
- `store(Request $request)` - Use SubjectRepository::create()
- `update(Request $request, $id)` - Use SubjectRepository::update()
- `destroy($id)` - Use SubjectRepository::delete()

**Form Requests to Create:**
- StoreSubjectRequest
- UpdateSubjectRequest

**Complexity:** Low (4 methods)
**Priority:** High

---

#### 5. AdminScheduleController

**Current Issues:**
- Direct Schedule model access
- Complex form validation

**Dependencies to Inject:**
- ScheduleRepositoryInterface
- SubjectRepositoryInterface
- TeacherRepositoryInterface
- SectionRepositoryInterface

**Methods to Refactor:**
- `index()` - Use ScheduleRepository for listing
- `store(Request $request)` - Use ScheduleRepository::create()
- `destroy($id)` - Use ScheduleRepository::delete()

**Form Requests to Create:**
- StoreScheduleRequest

**Complexity:** Medium (3 methods)
**Priority:** Medium

---

#### 6. AdminAttendanceController

**Current Issues:**
- Direct Attendance model access
- Attendance log viewing

**Dependencies to Inject:**
- AttendanceRepositoryInterface
- StudentRepositoryInterface
- ScheduleRepositoryInterface

**Methods to Refactor:**
- `index()` - Use AttendanceRepository for filtering and pagination

**Complexity:** Low (1 method)
**Priority:** Low

---

#### 7. AdminAnnouncementController

**Current Issues:**
- Direct Announcement model access
- File upload handling

**Dependencies to Inject:**
- AnnouncementRepositoryInterface
- NotificationServiceInterface

**Methods to Refactor:**
- `index()` - Use AnnouncementRepository for listing
- `store(Request $request)` - Use AnnouncementRepository and NotificationService
- `destroy($id)` - Use AnnouncementRepository::delete()

**Form Requests to Create:**
- StoreAnnouncementRequest

**Complexity:** Medium (3 methods)
**Priority:** Medium

---

### TEACHER CONTROLLERS

#### 8. TeacherController

**Current Issues:**
- Direct Student model access
- Direct Section model access

**Dependencies to Inject:**
- StudentRepositoryInterface
- SectionRepositoryInterface

**Methods to Refactor:**
- `index($sectionId)` - Use StudentRepository::getBySection()

**Complexity:** Low (1 method)
**Priority:** Low

---

#### 9. TeacherDashboardController

**Current Issues:**
- Direct model queries for statistics
- Business logic embedded

**Dependencies to Inject:**
- DashboardServiceInterface
- ScheduleRepositoryInterface

**Methods to Refactor:**
- `index()` - Use DashboardService::getTeacherDashboard()

**Complexity:** Low (1 method)
**Priority:** High

---

#### 10. GradeController

**Current Issues:**
- Direct Grade model access
- Direct Student model access
- Grade validation logic

**Dependencies to Inject:**
- GradeServiceInterface
- ScheduleRepositoryInterface

**Methods to Refactor:**
- `index($classId)` - Use GradeService::getClassGrades()
- `store(Request $request)` - Use GradeService::recordGrade()
- `update(Request $request, $id)` - Use GradeService::updateGrade()

**Form Requests to Create:**
- StoreGradeRequest
- UpdateGradeRequest

**Complexity:** Medium (3 methods)
**Priority:** High

---

#### 11. AssignmentController

**Current Issues:**
- Direct Assignment model access
- Direct Schedule model access
- File upload logic in controller
- Complex class selection logic

**Dependencies to Inject:**
- AssignmentServiceInterface
- ScheduleRepositoryInterface

**Methods to Refactor:**
- `index()` - Use AssignmentService::getAssignments() and ScheduleRepository
- `show($id)` - Use AssignmentService::getAssignmentDetails()
- `store(Request $request)` - Use AssignmentService::createAssignment()

**Form Requests to Create:**
- CreateAssignmentRequest

**Complexity:** High (3 methods with complex logic)
**Priority:** High

---

#### 12. TeacherAnnouncementController

**Current Issues:**
- Direct Announcement model access

**Dependencies to Inject:**
- AnnouncementRepositoryInterface
- NotificationServiceInterface

**Methods to Refactor:**
- `index()` - Use AnnouncementRepository for listing
- `store(Request $request)` - Use AnnouncementRepository and NotificationService
- `destroy($id)` - Use AnnouncementRepository::delete()

**Form Requests to Create:**
- StoreAnnouncementRequest (can reuse from Admin)

**Complexity:** Medium (3 methods)
**Priority:** Medium

---

#### 13. TeacherAuthController

**Current Issues:**
- Authentication logic in controller

**Dependencies to Inject:**
- AuthServiceInterface

**Methods to Refactor:**
- `login()` - Use AuthService::login()
- `logout()` - Use AuthService::logout()

**Form Requests to Create:**
- LoginRequest

**Complexity:** Low (2 methods)
**Priority:** Medium

---

### STUDENT CONTROLLERS

#### 14. StudentController

**Current Issues:**
- Direct Subject model access
- Direct Schedule model access
- PDF generation logic

**Dependencies to Inject:**
- GradeServiceInterface
- ScheduleRepositoryInterface
- ReportServiceInterface

**Methods to Refactor:**
- `grades()` - Use GradeService::getStudentGrades()
- `schedule()` - Use ScheduleRepository::getBySection()
- `downloadGrades()` - Use ReportService::generateReportCard()

**Complexity:** Medium (3 methods)
**Priority:** Medium

---

#### 15. StudentDashboardController

**Current Issues:**
- Direct model queries

**Dependencies to Inject:**
- DashboardServiceInterface

**Methods to Refactor:**
- `index()` - Use DashboardService::getStudentDashboard()

**Complexity:** Low (1 method)
**Priority:** High

---

#### 16. StudentProfileController

**Current Issues:**
- Direct Student model access
- File upload logic

**Dependencies to Inject:**
- StudentRepositoryInterface

**Methods to Refactor:**
- `edit()` - Use StudentRepository to get student data
- `update(Request $request)` - Use StudentRepository::update()

**Form Requests to Create:**
- UpdateProfileRequest

**Complexity:** Medium (2 methods)
**Priority:** High

---

#### 17. StudentAssignmentController

**Current Issues:**
- Direct Assignment model access
- Direct Submission model access
- File upload logic in controller

**Dependencies to Inject:**
- AssignmentServiceInterface
- SubmissionServiceInterface

**Methods to Refactor:**
- `index()` - Use AssignmentService::getAssignments()
- `submit(Request $request)` - Use SubmissionService::submitAssignment()

**Form Requests to Create:**
- SubmitAssignmentRequest

**Complexity:** Medium (2 methods)
**Priority:** High

---

#### 18. StudentAttendanceController

**Current Issues:**
- Direct Attendance model access

**Dependencies to Inject:**
- AttendanceServiceInterface

**Methods to Refactor:**
- `index()` - Use AttendanceService::getStudentAttendance()

**Complexity:** Low (1 method)
**Priority:** Low

---

### AUTH CONTROLLERS

#### 19. AuthController (Student)

**Current Issues:**
- Authentication logic in controller

**Dependencies to Inject:**
- AuthServiceInterface

**Methods to Refactor:**
- `login()` - Use AuthService::login()

**Form Requests to Create:**
- LoginRequest (can reuse from Teacher)

**Complexity:** Low (1 method)
**Priority:** Medium

---

#### 20. AuthController (General/Admin)

**If separate admin auth controller exists:**
- Similar refactoring using AuthServiceInterface

---

## Form Request Classes to Create

### Priority 1 (High)
1. **LoginRequest** - Student, Teacher, Admin login validation
2. **StoreStudentRequest** - Student creation validation
3. **UpdateStudentRequest** - Student update validation
4. **StoreTeacherRequest** - Teacher creation validation
5. **UpdateTeacherRequest** - Teacher update validation
6. **StoreSubjectRequest** - Subject creation validation
7. **UpdateSubjectRequest** - Subject update validation
8. **StoreGradeRequest** - Grade entry validation
9. **UpdateGradeRequest** - Grade update validation
10. **CreateAssignmentRequest** - Assignment creation validation
11. **SubmitAssignmentRequest** - Assignment submission validation

### Priority 2 (Medium)
12. **StoreScheduleRequest** - Schedule creation validation
13. **StoreAnnouncementRequest** - Announcement creation validation
14. **UpdateProfileRequest** - Student profile update validation

---

## Implementation Order

### Phase 1: Critical Controllers (Day 1) - Complete ✅
1. ✅ Teacher/TeacherController::dashboard() - High visibility
2. ✅ Student/StudentDashboardController - High visibility
3. ✅ Admin/AdminDashboardController - High visibility
4. ✅ Teacher/TeacherController grading methods - Core functionality
5. ✅ Student/StudentProfileController - Core functionality

### Phase 2: High-Priority Controllers (Day 2)
1. AdminStudentController - Core CRUD
2. AdminTeacherController - Core CRUD
3. AdminSubjectController - Core CRUD
4. AssignmentController - Core functionality
5. StudentAssignmentController - Core functionality

### Phase 3: Medium-Priority Controllers (Day 3)
1. AdminScheduleController - Management feature
2. AdminAnnouncementController - Management feature
3. TeacherAnnouncementController - Management feature
4. TeacherController - Viewing feature
5. StudentController - Viewing feature

### Phase 4: Remaining Controllers (Day 4)
1. AdminAttendanceController - Viewing feature
2. StudentAttendanceController - Viewing feature
3. TeacherAuthController - Authentication
4. AuthController (Student) - Authentication

### Phase 5: Form Requests & Testing (Day 5)
1. Create all Form Request classes
2. Test all refactored controllers
3. Fix any issues found

---

## Service Enhancements Required

### Existing Services (Already Implemented)
- ✅ DashboardService - All dashboard methods
- ✅ GradeService - All grade methods
- ✅ AttendanceService - All attendance methods
- ✅ AssignmentService - Most assignment methods
- ✅ SubmissionService - All submission methods
- ✅ NotificationService - All notification methods
- ✅ ReportService - All report methods
- ✅ AuthService - All auth methods

### Potential Service Gaps
**Review needed for:**
- StudentService - For complex student operations (profile updates, avatar upload)
- TeacherService - For complex teacher operations (profile updates)

---

## Testing Strategy

### Unit Testing
- Mock service interfaces in controller tests
- Test HTTP responses
- Test error handling

### Integration Testing
- Test complete user flows
- Verify database operations
- Check email sending

### Manual Testing Checklist
- [ ] All login/logout flows work
- [ ] All CRUD operations work
- [ ] File uploads work correctly
- [ ] Email notifications are sent
- [ ] PDF downloads work
- [ ] Search/filter functionality works
- [ ] Pagination works correctly
- [ ] Error messages display correctly

---

## Benefits Expected

### Code Quality
- 50-60% reduction in controller code
- Clear separation of concerns
- Consistent error handling
- Better testability

### Maintainability
- Business logic centralized in services
- Easy to add new features
- Easy to modify business rules
- Reduced code duplication

### Developer Experience
- Clear dependencies
- Easier to understand code
- Better IDE support
- Faster development

---

## Risk Mitigation

### Potential Issues
1. **Breaking functionality** - Gradual rollout, thorough testing
2. **Service method gaps** - Implement missing methods as needed
3. **Form request validation differences** - Match existing rules exactly
4. **File upload handling** - Ensure file storage logic preserved

### Mitigation Strategies
1. Keep old code as comments during refactoring
2. Test each controller immediately after refactoring
3. Run full application tests after each phase
4. Have rollback plan ready

---

## Success Criteria

- [ ] All 19 controllers refactored
- [ ] All controllers use dependency injection
- [ ] All business logic in service layer
- [ ] All form requests created
- [ ] All tests passing
- [ ] No regression bugs
- [ ] Code follows PSR-12
- [ ] Documentation updated

---

## Timeline Estimate

| Phase | Tasks | Controllers | Estimated Time |
|-------|-------|-------------|----------------|
| 1 | Critical Controllers | 5 | 1 day |
| 2 | High-Priority Controllers | 5 | 1 day |
| 3 | Medium-Priority Controllers | 5 | 1 day |
| 4 | Remaining Controllers | 4 | 1 day |
| 5 | Form Requests & Testing | 14 form requests + testing | 1 day |
| **Total** | | **19 controllers + 14 form requests** | **5 days** |

---

## Next Actions

1. Review this plan with stakeholders
2. Prioritize specific controllers if needed
3. Begin Phase 1 implementation
4. Track progress in checklist.md
5. Update progress.md after each controller refactored

---

**Document Version:** 1.0  
**Created:** January 22, 2026  
**Last Updated:** January 22, 2026  
**Status:** Ready for Implementation

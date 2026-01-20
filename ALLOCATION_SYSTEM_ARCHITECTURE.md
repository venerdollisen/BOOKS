# Allocation System Architecture & Integration

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    Vue 3 Application                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │            Sidebar Navigation                            │   │
│  │  ┌─ Setup & Configuration                               │   │
│  │  ├─── Departments ──────────┐                            │   │
│  │  ├─── Projects ────────────┐│                            │   │
│  │  └─── Subsidiary Accounts ─┤│                            │   │
│  └────────────────────────────┼┼────────────────────────────┘   │
│                               ││                                 │
│  ┌────────────────────────────┼┼────────────────────────────┐   │
│  │             Router          ││                           │   │
│  │  /setup/departments ────────┘│                           │   │
│  │  /setup/projects ───────────┐│                           │   │
│  │  /setup/subsidiary-accounts ┘└────────────────────────────┘  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │         View Components (Pages)                            │  │
│  │                                                              │  │
│  │  Departments.vue          Projects.vue        SubAccounts   │  │
│  │  ┌──────────────────┐    ┌──────────────┐    ┌────────┐    │  │
│  │  │ Header + Filters │    │ Header +     │    │ Header │    │  │
│  │  │ Search, Status   │    │ Search, Dept │    │ +      │    │  │
│  │  │ Data Table       │    │ Status Filter│    │ Filter │    │  │
│  │  │ Pagination       │    │ Data Table   │    │ Table  │    │  │
│  │  │ Delete Modal     │    │ Pagination   │    │ Delete │    │  │
│  │  └──────────────────┘    └──────────────┘    └────────┘    │  │
│  └────────────────────────────────────────────────────────────┘  │
│             ↓ (dispatch)              ↓ (dispatch)                │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │     Pinia Stores (State Management)                        │  │
│  │                                                              │  │
│  │  useDepartmentStore        useProjectStore   useSubAccount  │  │
│  │  ┌──────────────────┐    ┌──────────────┐    ┌────────┐    │  │
│  │  │ departments[]    │    │ projects[]   │    │ subAcct│    │  │
│  │  │ loading          │    │ loading      │    │ loading│    │  │
│  │  │ pagination       │    │ pagination   │    │ paging │    │  │
│  │  │ filters          │    │ filters      │    │ filters│    │  │
│  │  │                  │    │              │    │        │    │  │
│  │  │ fetch()          │    │ fetch()      │    │ fetch()│    │  │
│  │  │ create()         │    │ create()     │    │ create │    │  │
│  │  │ update()         │    │ update()     │    │ update │    │  │
│  │  │ delete()         │    │ delete()     │    │ delete │    │  │
│  │  └──────────────────┘    └──────────────┘    └────────┘    │  │
│  └────────────────────────────────────────────────────────────┘  │
│             ↓ (HTTP calls)            ↓ (HTTP calls)              │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │     API Service Functions                                  │  │
│  │                                                              │  │
│  │  departments.js           projects.js       subsidiary...   │  │
│  │  ┌──────────────────┐    ┌──────────────┐    ┌────────┐    │  │
│  │  │ getAll()         │    │ getAll()     │    │ getAll │    │  │
│  │  │ getById()        │    │ getById()    │    │ getById│    │  │
│  │  │ create()         │    │ create()     │    │ create │    │  │
│  │  │ update()         │    │ update()     │    │ update │    │  │
│  │  │ delete()         │    │ delete()     │    │ delete │    │  │
│  │  │                  │    │              │    │        │    │  │
│  │  │ (w/ Axios HTTP)  │    │ (w/ Axios)   │    │ Axios  │    │  │
│  │  └──────────────────┘    └──────────────┘    └────────┘    │  │
│  └────────────────────────────────────────────────────────────┘  │
│             ↓ (HTTP Requests)         ↓ (HTTP Requests)           │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
                              ↓
        ┌───────────────────────────────────────────┐
        │      Laravel Backend API Server           │
        │      (http://localhost:8000/api)          │
        ├───────────────────────────────────────────┤
        │                                            │
        │  Controllers:                              │
        │  • DepartmentController                    │
        │  • ProjectController                       │
        │  • SubsidiaryAccountController             │
        │                                            │
        │  Models (Eloquent):                        │
        │  • Department                              │
        │  • Project                                 │
        │  • SubsidiaryAccount                       │
        │  • TransactionItem (with relationships)    │
        │                                            │
        │  Endpoints:                                │
        │  GET    /api/departments                   │
        │  POST   /api/departments                   │
        │  GET    /api/departments/{id}              │
        │  PUT    /api/departments/{id}              │
        │  DELETE /api/departments/{id}              │
        │  + similar for projects & subsidiary...    │
        │                                            │
        └───────────────────────────────────────────┘
                              ↓
        ┌───────────────────────────────────────────┐
        │      SQLite Database                       │
        │                                            │
        │  Tables:                                   │
        │  • departments                             │
        │  • projects                                │
        │  • subsidiary_accounts                     │
        │  • transaction_items (with FK references)  │
        │  • accounts (chart of accounts)            │
        │  • transactions                            │
        │                                            │
        └───────────────────────────────────────────┘
```

## Component Interaction Flow

### User Workflow: Create Department

```
User Interface
    ↓
[Click "New Department" button in Departments.vue]
    ↓
DepartmentForm.vue Modal Opens
    ↓
[Fill form fields and click "Create"]
    ↓
Form validates and emits 'save' event
    ↓
Departments.vue handler receives event
    ↓
calls: departmentStore.createDepartment(formData)
    ↓
Store action executes:
  1. Set loading = true
  2. Call departmentsService.create(formData)
    ↓
Service makes HTTP request:
  POST /api/departments with formData
    ↓
Backend processes request
  1. Validates data
  2. Creates new record
  3. Saves to database
  4. Returns JSON response with new department
    ↓
Service receives response
  Returns Promise with data
    ↓
Store receives data
  1. Add to departments array
  2. Set loading = false
    ↓
Component detects state change
  (reactive update via Pinia)
    ↓
Component handler completes
  1. Modal closes
  2. Table refreshes
    ↓
User sees new department in table
```

## File Structure

```
src/
├── views/
│   ├── Departments.vue          ← Department management page
│   ├── Projects.vue             ← Project management page
│   └── SubsidiaryAccounts.vue   ← Subsidiary accounts page
│
├── components/
│   └── Setup/
│       ├── DepartmentForm.vue   ← Create/Edit modal
│       ├── ProjectForm.vue      ← Create/Edit modal
│       └── SubsidiaryAccountForm.vue ← Create/Edit modal
│
├── stores/
│   ├── departments.js           ← Department state management
│   ├── projects.js              ← Project state management
│   └── subsidiaryAccounts.js    ← Subsidiary account state
│
├── services/
│   ├── departments.js           ← Department API client
│   ├── projects.js              ← Project API client
│   └── subsidiaryAccounts.js    ← Subsidiary account API client
│
├── router/
│   └── index.js                 ← Routes (updated with new routes)
│
└── components/
    └── Layout/
        └── Sidebar.vue          ← Navigation menu (updated)
```

## Data Models

### Department Model
```javascript
{
  id: 1,
  code: "SALES",
  name: "Sales Department",
  manager: "John Doe",
  budget: 500000,
  status: "active",
  created_at: "2026-01-20T10:00:00Z",
  updated_at: "2026-01-20T10:00:00Z"
}
```

### Project Model
```javascript
{
  id: 1,
  name: "Website Redesign",
  department_id: 1,
  department: { id: 1, code: "SALES", name: "Sales Department" },
  start_date: "2026-01-15",
  end_date: "2026-03-31",
  budget: 100000,
  status: "active",
  created_at: "2026-01-20T10:00:00Z",
  updated_at: "2026-01-20T10:00:00Z"
}
```

### SubsidiaryAccount Model
```javascript
{
  id: 1,
  code: "SAL-DOM",
  name: "Domestic Sales",
  account_id: 4001,
  account: { id: 4001, code: "4001", name: "Sales Revenue" },
  type: "cost_center",
  status: "active",
  created_at: "2026-01-20T10:00:00Z",
  updated_at: "2026-01-20T10:00:00Z"
}
```

## Store State Example

### Departments Store State
```javascript
{
  departments: [
    { id: 1, code: "SALES", name: "Sales", ... },
    { id: 2, code: "OPS", name: "Operations", ... },
    { id: 3, code: "IT", name: "IT", ... },
    ...
  ],
  currentDepartment: null,
  loading: false,
  error: null,
  pagination: {
    page: 1,
    per_page: 10,
    total: 15,
    last_page: 2
  },
  filters: {
    search: "",
    status: ""
  }
}
```

## API Request/Response Examples

### Create Department
```
REQUEST:
POST /api/departments
Content-Type: application/json
Authorization: Bearer {token}

{
  "code": "NEW_DEPT",
  "name": "New Department",
  "manager": "Jane Smith",
  "budget": 250000,
  "status": "active"
}

RESPONSE (201 Created):
{
  "data": {
    "id": 7,
    "code": "NEW_DEPT",
    "name": "New Department",
    "manager": "Jane Smith",
    "budget": 250000,
    "status": "active",
    "created_at": "2026-01-20T15:30:00Z",
    "updated_at": "2026-01-20T15:30:00Z"
  }
}
```

### List Departments with Filters
```
REQUEST:
GET /api/departments?page=1&per_page=10&search=sales&status=active
Content-Type: application/json
Authorization: Bearer {token}

RESPONSE (200 OK):
{
  "data": [
    { id: 1, code: "SALES", name: "Sales", ... },
    { id: 2, code: "SALES_INTL", name: "International Sales", ... }
  ],
  "current_page": 1,
  "per_page": 10,
  "total": 2,
  "last_page": 1
}
```

## Routing Map

```
Application Routes:
/                           → Dashboard
/accounts                   → Chart of Accounts
/transactions               → Transactions
├── /transactions/cash-bank → Cash/Bank Transactions
├── /transactions/journal   → Journal Entries
├── /transactions/sales     → Sales/Revenue
├── /transactions/purchases → Purchases/Expenses
└── /transactions/recurring → Recurring Transactions
/receivables                → Accounts Receivable
/payables                   → Accounts Payable
/reports                    → Financial Reports
├── /reports/balance-sheet
├── /reports/profit-loss
├── /reports/cash-flow
└── /reports/trial-balance
/payroll                    → Payroll/Employees
/inventory                  → Inventory/Products
/setup                      → Setup & Configuration ← NEW
├── /setup/departments      → Departments (NEW)
├── /setup/projects         → Projects (NEW)
└── /setup/subsidiary-accounts → Subsidiary Accounts (NEW)
/settings                   → Settings/Administration
/login                      → Login
```

## Sidebar Menu Structure

```
Sidebar Menu
├── Dashboard
├── Chart of Accounts
├── Transactions
│   ├── Cash/Bank
│   ├── Journal Entries
│   ├── Sales/Revenue
│   ├── Purchases/Expenses
│   └── Recurring Transactions
├── Accounts Receivable
├── Accounts Payable
├── Financial Reports
│   ├── Balance Sheet
│   ├── Profit & Loss
│   ├── Cash Flow
│   └── Trial Balance
├── Payroll/Employees
├── Inventory/Products
├── Setup & Configuration ← NEW
│   ├── Departments ← NEW
│   ├── Projects ← NEW
│   └── Subsidiary Accounts ← NEW
├── Settings/Administration
└── Logout (in user menu)
```

## Data Flow Patterns

### Pattern 1: List with Pagination
```
Component Mount
  ↓
store.fetchDepartments()
  ↓
Store.loading = true
  ↓
service.getAll(page, limit, filters)
  ↓
API GET /departments
  ↓
Store receives response
  ↓
Store.departments = data
Store.pagination = { page, total, ... }
Store.loading = false
  ↓
Component renders table
```

### Pattern 2: Filter/Search
```
User enters search term
  ↓
Component calls store.setFilters({ search: term })
  ↓
Store updates filters
  ↓
Store resets to page 1
  ↓
Store calls fetchDepartments(1)
  ↓
API called with search parameter
  ↓
Filtered results returned
  ↓
Table updates automatically
```

### Pattern 3: Delete with Confirmation
```
User clicks Delete button
  ↓
Component shows delete confirmation modal
  ↓
User confirms deletion
  ↓
Component calls store.deleteDepartment(id)
  ↓
Store.loading = true
  ↓
service.delete(id)
  ↓
API DELETE /departments/:id
  ↓
Store removes from departments array
Store.loading = false
  ↓
Component closes modal
  ↓
Table re-renders without deleted item
```

## Performance Considerations

1. **Pagination:** Limited to 10 items per page to reduce initial load
2. **Lazy Loading:** Forms and views are lazy-loaded via router
3. **Efficient Filtering:** Server-side filtering reduces data transfer
4. **State Management:** Pinia maintains single source of truth
5. **Reactive Updates:** Vue's reactivity system minimizes DOM updates
6. **Loading States:** Users see feedback during API calls

## Security Considerations

1. **Authentication:** All endpoints require Bearer token in Authorization header
2. **CORS:** API configured for cross-origin requests from frontend
3. **Data Validation:** Backend validates all input before database operations
4. **Authorization:** Backend checks user permissions for sensitive operations
5. **Error Handling:** Sensitive error details not exposed to frontend

## Integration with Transactions

The allocation system integrates with the transaction system through the `transaction_items` table, which now has three new foreign key columns:
- `department_id` → Allocate expense/revenue to department
- `project_id` → Allocate to specific project
- `subsidiary_account_id` → Allocate to cost center/branch/division

This enables every transaction line item to be tracked across multiple organizational dimensions for comprehensive reporting and analysis.

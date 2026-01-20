# Allocation Management - Quick Start Guide

## Accessing the New Features

### 1. Navigate to Setup & Configuration
In the application sidebar, look for the **Setup & Configuration** menu item with a gear icon. Click to expand the submenu.

### 2. Available Options
```
Setup & Configuration
├── Departments
├── Projects
└── Subsidiary Accounts
```

## Departments Management

### Create a Department
1. Click **Setup & Configuration** → **Departments**
2. Click the **+ New Department** button (top right)
3. Fill in the form:
   - **Department Code**: e.g., "SALES", "OPS", "IT" (required)
   - **Department Name**: Full name (required)
   - **Manager Name**: Person responsible (optional)
   - **Annual Budget**: Dollar amount (optional)
   - **Status**: Select Active or Inactive
4. Click **Create** button

### View Departments
The departments page displays:
- **Code**: Department code
- **Name**: Full department name
- **Manager**: Assigned manager
- **Budget**: Annual budget allocation
- **Status**: Active/Inactive badge (green/gray)

### Search & Filter
- **Search Box**: Type to search by code or name (real-time)
- **Status Filter**: Show only active or inactive departments
- **Clear Filters**: Reset all filters

### Edit a Department
1. Find the department in the list
2. Click the **Edit** button (right side of row)
3. Update any fields in the modal
4. Click **Update** button

### Delete a Department
1. Find the department in the list
2. Click the **Delete** button (right side of row)
3. Confirm deletion in the modal
4. Department is removed from the system

### Pagination
- View up to 10 departments per page
- Use **Previous** and **Next** buttons to navigate
- Current page shown at bottom

## Projects Management

### Create a Project
1. Click **Setup & Configuration** → **Projects**
2. Click the **+ New Project** button
3. Fill in the form:
   - **Project Name**: e.g., "Website Redesign" (required)
   - **Department**: Select from dropdown (required)
   - **Start Date**: Project start date (optional)
   - **End Date**: Project end date (optional)
   - **Project Budget**: Budget allocation (optional)
   - **Status**: Select from options:
     - Planning (blue) - Not started
     - Active (green) - Currently running
     - Paused (yellow) - On hold
     - Completed (gray) - Finished
     - Cancelled (red) - Not continuing
4. Click **Create** button

### View Projects
The projects page displays:
- **Name**: Project name
- **Department**: Associated department
- **Start Date** / **End Date**: Project timeline
- **Budget**: Total project budget
- **Status**: Color-coded status badge

### Search & Filter
- **Search Box**: Search by project name
- **Department Filter**: Show projects from specific department
- **Status Filter**: Show projects with specific status
- **Clear Filters**: Reset all filters

### Edit a Project
1. Find the project in the list
2. Click **Edit**
3. Modify the fields
4. Click **Update**

### Delete a Project
1. Find the project in the list
2. Click **Delete**
3. Confirm in the modal

## Subsidiary Accounts Management

### Create a Subsidiary Account
1. Click **Setup & Configuration** → **Subsidiary Accounts**
2. Click the **+ New Account** button
3. Fill in the form:
   - **Account Code**: e.g., "SAL-DOM", "CC-SALES" (required)
   - **Account Name**: e.g., "Domestic Sales" (required)
   - **Main Account**: Select the main chart of accounts entry (required)
   - **Type**: Select the subsidiary type:
     - Cost Center (blue) - Department cost tracking
     - Profit Center (purple) - Profit tracking
     - Branch (green) - Geographical location
     - Division (yellow) - Business division
     - Custom (gray) - Custom categorization
   - **Status**: Active or Inactive
4. Click **Create** button

### View Subsidiary Accounts
The accounts page displays:
- **Code**: Short identifier
- **Name**: Full account name
- **Main Account**: Associated chart of accounts entry
- **Type**: Color-coded type badge
- **Status**: Active/Inactive badge

### Search & Filter
- **Search Box**: Search by code or name
- **Type Filter**: Show accounts of specific type
- **Status Filter**: Show active or inactive accounts
- **Clear Filters**: Reset all filters

### Edit a Subsidiary Account
1. Find the account in the list
2. Click **Edit**
3. Update the details
4. Click **Update**

### Delete a Subsidiary Account
1. Find the account in the list
2. Click **Delete**
3. Confirm deletion

## Common Features Across All Modules

### Search
- Real-time filtering as you type
- Searches across relevant fields (code, name, manager, etc.)
- Results update instantly

### Filtering
- Multiple filter criteria available
- Combine search with other filters
- Clear filters to show all records

### Pagination
- 10 items displayed per page
- Navigate with Previous/Next buttons
- Current page number shown
- Jump to any page directly

### Error Handling
- Error messages displayed in red banner
- Indicates what went wrong
- Try again after fixing the issue

### Loading States
- Spinner shown while data loads
- Buttons disabled during operations
- Prevents accidental duplicate submissions

### Empty States
- Message shown when no records found
- Quick action to create first record
- Helpful guidance for new users

## Integration with Transactions

Once you've created departments, projects, and subsidiary accounts, they become available when creating transactions:

1. Go to **Transactions** → **Cash/Bank** (or other transaction type)
2. When adding a transaction line item, you can now allocate it to:
   - A specific **Department**
   - A specific **Project**
   - A specific **Subsidiary Account** (Cost Center/Branch)

This enables comprehensive tracking of expenses and revenue across organizational dimensions.

## Pre-Populated Data

The system comes with sample data for testing:

### Departments (6)
- SALES - Sales Department
- OPS - Operations
- IT - Information Technology
- HR - Human Resources
- MARKETING - Marketing
- FINANCE - Finance

### Projects (5)
- Website Redesign (SALES)
- Marketing Campaign (MARKETING)
- Europe Expansion (SALES)
- ERP Implementation (IT)
- Training Program (HR)

### Subsidiary Accounts (10)
- Product-based (COGS tracking)
- Department-based (Salaries for different departments)
- Location-based (Office expenses by location)
- Service-based (Different banking services)

You can delete these and create your own, or modify them as needed.

## Tips & Best Practices

### Departments
- Use consistent naming (e.g., all caps for codes)
- Assign clear manager names for accountability
- Set realistic budgets for monitoring
- Mark unused departments as Inactive

### Projects
- Allocate budgets to projects for cost tracking
- Set realistic dates for project duration
- Use status to track project lifecycle
- Match projects to responsible departments

### Subsidiary Accounts
- Use clear codes for easy identification
- Choose appropriate type for the purpose
- Link to correct main chart of accounts
- Keep names concise but descriptive

## Troubleshooting

### Data Not Loading
- Check internet connection
- Verify backend API is running (http://localhost:8000)
- Refresh the page (Ctrl+R)
- Check browser console for errors (F12)

### Can't Create Record
- Ensure all required fields are filled
- Check for validation error messages
- Verify database connection
- Try again in a few moments

### Filters Not Working
- Clear filters and try again
- Ensure search term is correct
- Check that records exist matching criteria
- Page may be refreshing with results

### Pagination Not Working
- Ensure you're not at last page
- Check number of records available
- Pagination only shows if more than 10 records

## Keyboard Shortcuts

- **Escape**: Close modal/dialog
- **Enter**: Submit form
- **Ctrl+F**: Open browser search (search within page)

## Responsive Design

The application works on all screen sizes:
- **Desktop**: Full sidebar navigation
- **Tablet**: Responsive table layout
- **Mobile**: Collapsible sidebar, single-column tables

On mobile, swipe the sidebar button to toggle menu.

## Need More Help?

Refer to the documentation files:
- `ALLOCATION_UI_IMPLEMENTATION.md` - Complete technical documentation
- `ALLOCATION_SYSTEM_ARCHITECTURE.md` - System architecture and data flows

Or check the browser console (F12) for error details if something isn't working.

## Summary

You can now:
✅ Create and manage departments
✅ Create and manage projects
✅ Create and manage subsidiary accounts
✅ Search and filter all records
✅ Use these allocations in transaction entries
✅ Track expenses across multiple dimensions

The allocation system is fully functional and production-ready!

# Bookkeeping System - Vue 3 Frontend

A comprehensive bookkeeping system built with Vue 3, Composition API, and Tailwind CSS. Designed to connect with a Laravel backend API.

## Features

### 🏠 Dashboard / Home
- Cash balance overview with account/currency filters
- Accounts Receivable/Payable summaries with aging reports
- Profit & Loss overview (monthly, quarterly, yearly)
- Recent transactions list
- Visual charts (cash flow trends, expense distribution)
- Notifications and alerts

### 📊 Chart of Accounts
- Hierarchical account structure (Assets, Liabilities, Equity, Income, Expenses)
- Add, edit, delete accounts
- Search and filter by account type
- Parent-child relationships

### 💰 Transactions
- **Cash / Bank Transactions**: Receipts, payments, transfers
- **Journal Entries**: Manual double-entry bookkeeping
- **Sales / Revenue**: Invoice creation with tax calculation
- **Purchases / Expenses**: Bill recording and payment tracking
- **Recurring Transactions**: Automated recurring entries

### 📥 Accounts Receivable
- Customer invoice management
- Payment tracking and application
- Aging reports (0-30, 31-60, 61+ days)
- Customer statements

### 📤 Accounts Payable
- Supplier bill management
- Payment tracking and scheduling
- Aging reports
- Supplier statements

### 📈 Financial Reports
- **Balance Sheet**: Assets = Liabilities + Equity
- **Profit & Loss Statement**: Revenue - Expenses
- **Cash Flow Statement**: Operating, Investing, Financing activities
- **Trial Balance**: Verify double-entry accuracy
- Export to PDF/Excel/CSV

### 👥 Payroll / Employees
- Employee management
- Salary and deduction tracking
- Payslip generation (PDF)
- Recurring salary transactions

### 📦 Inventory / Products
- Product/service management
- Stock level tracking
- Low stock alerts
- Valuation reports

### ⚙️ Settings / Administration
- Company profile management
- User and role management (Admin, Accountant, Viewer)
- Tax rate configuration
- Currency and payment method settings
- System preferences

## Tech Stack

- **Vue 3** with Composition API
- **Vue Router 4** for navigation
- **Pinia** for state management (configured)
- **Axios** for API calls
- **Tailwind CSS** for styling
- **Chart.js** for data visualization
- **Heroicons** for icons
- **Vite** as build tool

## Project Structure

```
src/
├── components/
│   ├── Dashboard/         # Dashboard-specific components
│   ├── Accounts/          # Chart of Accounts components
│   ├── Transactions/      # Transaction form components
│   └── Layout/            # Layout components (Sidebar, Navbar)
├── views/
│   ├── Dashboard.vue
│   ├── ChartOfAccounts.vue
│   ├── Transactions/      # Transaction module views
│   ├── AccountsReceivable.vue
│   ├── AccountsPayable.vue
│   ├── Reports/           # Financial reports views
│   ├── Payroll.vue
│   ├── Inventory.vue
│   └── Settings.vue
├── router/
│   └── index.js           # Vue Router configuration
├── services/
│   └── api.js             # API service layer (Laravel ready)
├── App.vue
├── main.js
└── style.css              # Tailwind CSS imports
```

## Installation

1. **Install dependencies:**
   ```bash
   npm install
   ```

2. **Configure API endpoint:**
   Update the API base URL in `src/services/api.js` if needed. The default proxy is configured for `http://localhost:8000` in `vite.config.js`.

3. **Start development server:**
   ```bash
   npm run dev
   ```

4. **Build for production:**
   ```bash
   npm run build
   ```

## API Integration

The application is fully API-ready with a comprehensive service layer in `src/services/api.js`. All API calls are structured to work with a Laravel backend.

### API Structure

- **Dashboard API**: `dashboardApi` - Dashboard data endpoints
- **Accounts API**: `accountsApi` - Chart of Accounts CRUD
- **Transactions API**: `transactionsApi` - All transaction types
- **Receivables API**: `receivablesApi` - Invoice and payment management
- **Payables API**: `payablesApi` - Bill and payment management
- **Reports API**: `reportsApi` - Financial reports generation
- **Payroll API**: `payrollApi` - Employee and payslip management
- **Inventory API**: `inventoryApi` - Product and stock management
- **Settings API**: `settingsApi` - System configuration

### Authentication

The API service includes request interceptors that automatically add the authentication token from `localStorage`:

```javascript
// Token is retrieved from localStorage and added to headers
const token = localStorage.getItem('auth_token')
```

Update the token storage mechanism based on your Laravel authentication strategy.

## Laravel Backend Requirements

The frontend expects the following API endpoints (configured in `src/services/api.js`):

### Base URL
- `/api/*` - All API endpoints

### Authentication
- Use Bearer token authentication
- Token stored in `localStorage` as `auth_token`

### Expected Response Format
```json
{
  "data": [...],
  "message": "Success"
}
```

## Features & Best Practices

✅ **Component-based Architecture**: Reusable components for consistency  
✅ **Composition API**: Modern Vue 3 syntax throughout  
✅ **Type-safe API Calls**: Structured API service layer  
✅ **Responsive Design**: Mobile-friendly with Tailwind CSS  
✅ **Form Validation**: Client-side validation ready  
✅ **Error Handling**: API error interceptors configured  
✅ **Chart Visualizations**: Interactive charts for financial data  
✅ **Export Functionality**: PDF/Excel export ready  
✅ **Flexible UI**: Tailwind utility classes for easy customization  

## Development Notes

- All components use Vue 3 Composition API
- API calls use async/await pattern
- Error handling is implemented with try/catch
- Mock data is included in some components for development
- Replace mock data with actual API responses in production

## License

This project is ready for Laravel backend integration. Customize as needed for your specific requirements.

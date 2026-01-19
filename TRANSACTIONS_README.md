# Transactions Feature - README

## Quick Start

### 1. Setup Database
```bash
cd backend
php artisan migrate:fresh --seed
```

This will:
- Create all tables (accounts, transactions, transaction_items, users, etc.)
- Seed 30+ chart of accounts
- Seed 7 sample transactions with various statuses
- Create test user: `test@example.com` / `password123`

### 2. Start Backend Server
```bash
php artisan serve --port=8000
```

### 3. Start Frontend Dev Server
```bash
cd ..
npm run dev
```

### 4. Open Browser
- Go to `http://localhost:5173`
- Click "Transactions" in the sidebar
- You'll see the datatable with 7 sample transactions

## Testing the Feature

### View Transactions
1. Open the Transactions page
2. See list of all transactions with:
   - Reference number
   - Date
   - Type (Receipt/Payment/Journal/Transfer)
   - Status (Draft/Pending/Approved/Rejected)
   - Amount
   - Description

### Search & Filter
- **Search**: Type to find by reference or description
- **Type Filter**: Select Receipt, Payment, Journal, or Transfer
- **Status Filter**: Show only specific statuses
- **Date Range**: Filter by start and end dates
- **Per-Page**: Choose 10, 20, 50, or 100 items per page
- **Clear Filters**: Reset all filters at once

### Sort Columns
- Click any column header to sort
- Click again to reverse order
- Sort indicators (↑↓) show current sort direction

### Create Transaction
1. Click "+ New Transaction" button
2. Fill in form:
   - Reference (unique ID like REC-2025-001)
   - Date (transaction date)
   - Type (Receipt, Payment, Journal, or Transfer)
   - Amount (total transaction amount)
   - Status (Draft or Pending)
   - Description and notes (optional)
3. Add line items:
   - Select account for each line
   - Choose Debit or Credit
   - Enter amount
   - Add description (optional)
4. System shows balance indicator:
   - "✓ Balanced" when debits = credits (required)
   - "✗ Not Balanced" if amounts don't match
5. Click "Create Transaction" to save

### Edit Transaction
1. Click ✎ (edit icon) on any draft transaction
2. Form opens with current data
3. Modify any fields
4. Update line items if needed
5. System re-validates double-entry
6. Click "Update Transaction" to save

### View Details
1. Click 👁 (view icon) on any transaction
2. Modal shows full details:
   - All transaction info
   - Complete line items table
   - Balance verification
   - Created by and date
3. Status-specific actions available:
   - **Draft**: Edit, Delete
   - **Pending**: Approve, Reject
   - **Approved/Rejected**: View only

### Approve/Reject
For pending transactions:
1. Click 👁 (view) to open details
2. Click "Approve" button (makes it final)
   OR
   Click "Reject" button and enter reason
3. If rejected, can edit and resubmit

### Delete Transaction
1. Only draft and rejected transactions can be deleted
2. Click 🗑 (delete icon)
3. Confirm deletion
4. Transaction is soft-deleted (data preserved)

## Understanding the Data

### Transaction Structure
Each transaction has:
- **Header Info**: Reference, Date, Type, Status, Amount, Description
- **Line Items**: Accounts affected with debit/credit amounts
- **Metadata**: Creator, timestamps

### Double-Entry Accounting
Every transaction must balance:
```
Debits (↑) Total = Credits (↓) Total

Example - Receipt (Income):
  Debit:  Bank Account    $5,000
  Credit: Service Income  $5,000
         ─────────────────────
         Total: $5,000 = $5,000 ✓
```

### Transaction Status Flow
```
1. DRAFT
   - Editable
   - Only you can see it
   - Can change to PENDING

2. PENDING
   - Submitted for approval
   - Not editable
   - Awaiting manager decision

3. APPROVED
   - Final and locked
   - Cannot be edited
   - Posted to GL

4. REJECTED
   - Can be fixed and resubmitted
   - Can be deleted
   - Shows rejection reason
```

## API Reference

### List Transactions
```bash
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/transactions?page=1&status=draft"
```

### Create Transaction
```bash
curl -X POST http://localhost:8000/api/transactions \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reference": "REC-2025-001",
    "transaction_date": "2025-01-20",
    "type": "receipt",
    "status": "draft",
    "amount": 5000,
    "items": [
      {"account_id": 1, "type": "debit", "amount": 5000},
      {"account_id": 10, "type": "credit", "amount": 5000}
    ]
  }'
```

### Approve Transaction
```bash
curl -X POST http://localhost:8000/api/transactions/1/approve \
  -H "Authorization: Bearer TOKEN"
```

### Reject Transaction
```bash
curl -X POST http://localhost:8000/api/transactions/1/reject \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"reason": "Needs corrections"}'
```

See **TRANSACTIONS_API_GUIDE.md** for complete API documentation.

## Component Structure

### Transactions.vue (Main View)
The main page showing all transactions in a datatable

**Features**:
- List with pagination
- Search and filters
- Sortable columns
- Status-specific actions

### TransactionForm.vue (Modal)
Form for creating and editing transactions

**Features**:
- Automatic validation
- Dynamic line items
- Balance checking
- Double-entry enforcement

### TransactionDetails.vue (Modal)
View transaction details and perform actions

**Features**:
- Full details display
- Line items table
- Approve/reject buttons
- Rejection reason capture

## Database Schema

### transactions Table
```
Stores main transaction record:
- reference: Unique ID (REC-2025-001)
- type: receipt, payment, journal, transfer
- status: draft, pending, approved, rejected
- amount: Total transaction amount
- created_at, updated_at, deleted_at: Timestamps
```

### transaction_items Table
```
Stores line items (debits and credits):
- transaction_id: Links to transaction
- account_id: Which account affected
- type: debit or credit
- amount: Amount for this line
```

## Validation Rules

### Transaction Fields
- **Reference**: Required, must be unique
- **Date**: Required, must be valid date
- **Type**: Required, must be valid type
- **Amount**: Required, must be > 0
- **Items**: Required, must have at least 1 item
- **Double-Entry**: Total debits must equal total credits

### Line Item Fields
- **Account**: Required, must exist in database
- **Type**: Required, must be debit or credit
- **Amount**: Required, must be > 0
- **Description**: Optional

## Common Errors

### "Debits must equal credits"
- The amounts in your line items don't balance
- Check the real-time balance indicator
- Add/remove items until balanced

### "Reference has already been taken"
- That reference number already exists
- Use a unique reference
- Format: TYPE-YYYY-NNN (REC-2025-001)

### "Cannot edit non-draft transaction"
- Only draft transactions are editable
- Submit to pending, then only approve/reject options
- Can only edit rejected after fixing

### "Only draft transactions can be deleted"
- Approved/pending can't be deleted
- If mistake, reject it first, then delete
- Rejected status allows deletion

## Tips & Tricks

### Organizing References
Use a consistent format:
- `REC-2025-001` for receipts (income)
- `PAY-2025-001` for payments (expenses)
- `JNL-2025-001` for journal entries
- `TRF-2025-001` for transfers

### Working with Line Items
1. Accounts appear in dropdown from Chart of Accounts
2. Always have at least one debit and one credit
3. Balance will show in real-time
4. Can have multiple debits/credits as long as total balances

### Workflow Best Practices
1. Create as DRAFT first (no approval needed)
2. Review all details carefully
3. Submit to PENDING for approval
4. Manager approves (final) or rejects (can fix)
5. If rejected, edit and resubmit
6. Once approved, can't edit (prevents fraud)

### Search Tips
- Search by partial reference: type "REC-" to find all receipts
- Search by description: type customer name
- Combine with filters for specific subsets
- Use date range for period lookups

### Performance
- List page handles thousands of transactions
- Search is fast with indexing
- Sorting happens server-side (efficient)
- Pagination prevents loading huge datasets

## Troubleshooting

### Page shows "No transactions found"
- Database may not be seeded
- Run: `php artisan migrate:fresh --seed`
- Check backend server is running

### Can't login
- Default user: test@example.com
- Default password: password123
- Or register a new user

### "Transaction failed" error
- Check network tab in browser DevTools
- Verify backend server is running
- Check validation errors in response

### Can't add line items
- Click "+ Add Line Item" button
- If missing, refresh page
- Check that accounts exist in Chart of Accounts

### Balance won't show as balanced
- Sum of debits must equal sum of credits
- Check amounts carefully
- Each field must be > 0
- No negative amounts allowed

## Feature Limitations & Notes

### Current Version (v1.0)
- Single-user creation (user_id auto-set to logged-in user)
- No bulk operations (approve/delete multiple)
- No document attachments
- No audit log of changes
- Soft deletes only (no permanent delete option)

### Future Enhancements
- Bulk approve/reject
- Transaction templates
- PDF/Excel export
- Receipt/document attachments
- Change audit log
- Advanced reconciliation
- GL posting integration
- Trial balance generation

## Support & Documentation

### Quick References
- **API Guide**: See TRANSACTIONS_API_GUIDE.md
- **Implementation**: See TRANSACTIONS_IMPLEMENTATION.md
- **Architecture**: See TRANSACTION_ARCHITECTURE.md
- **Complete Guide**: See TRANSACTIONS_COMPLETE.md

### Code Locations
```
Backend:
  API: backend/app/Http/Controllers/TransactionController.php
  Models: backend/app/Models/Transaction.php
  Models: backend/app/Models/TransactionItem.php
  
Frontend:
  Store: src/stores/transactions.js
  Views: src/views/Transactions.vue
  Form: src/components/Transactions/TransactionForm.vue
  Details: src/components/Transactions/TransactionDetails.vue
```

## Version History

### v1.0 (Current)
- ✅ Full CRUD operations
- ✅ Server-side datatable
- ✅ Double-entry validation
- ✅ Status workflow
- ✅ Pagination, search, filter, sort
- ✅ Multi-type support
- ✅ 7 API endpoints

## Questions?

Refer to the comprehensive documentation files:
1. **START_HERE.md** - Getting started guide
2. **TRANSACTIONS_API_GUIDE.md** - API reference
3. **TRANSACTION_ARCHITECTURE.md** - System design
4. **PROJECT_STATUS.md** - Overall project status

---

**Ready to use! Happy transaction management! 🎉**

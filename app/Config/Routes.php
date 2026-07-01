<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'AuthController::login');
$routes->get('login', 'AuthController::login');
$routes->post('attemptLogin', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');
$routes->get('lang/(:segment)', 'Home::lang/$1');



// Dashboard protected group
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Home::index');


    // Profile
    $routes->get('profile/change_password', 'AuthController::changePassword');
    $routes->post('profile/change_password', 'AuthController::updatePassword');

    // Countries
    $routes->get('countries', 'CountryController::index', ['filter' => 'permission:country.view']);
    $routes->get('countries/create', 'CountryController::create', ['filter' => 'permission:country.create']);
    $routes->post('countries/store', 'CountryController::store', ['filter' => 'permission:country.create']);
    $routes->get('countries/edit/(:num)', 'CountryController::edit/$1', ['filter' => 'permission:country.edit']);
    $routes->post('countries/update/(:num)', 'CountryController::update/$1', ['filter' => 'permission:country.edit']);
    $routes->get('countries/delete/(:num)', 'CountryController::delete/$1', ['filter' => 'permission:country.delete']);

    // States
    $routes->get('states', 'StateController::index', ['filter' => 'permission:state.view']);
    $routes->get('states/create', 'StateController::create', ['filter' => 'permission:state.create']);
    $routes->post('states/store', 'StateController::store', ['filter' => 'permission:state.create']);
    $routes->get('states/edit/(:num)', 'StateController::edit/$1', ['filter' => 'permission:state.edit']);
    $routes->post('states/update/(:num)', 'StateController::update/$1', ['filter' => 'permission:state.edit']);
    $routes->get('states/delete/(:num)', 'StateController::delete/$1', ['filter' => 'permission:state.delete']);

    // Agents
    $routes->get('agents', 'AgentController::index', ['filter' => 'permission:agent.view']);
    $routes->get('agents/create', 'AgentController::create', ['filter' => 'permission:agent.create']);
    $routes->post('agents/store', 'AgentController::store', ['filter' => 'permission:agent.create']);
    $routes->get('agents/view/(:num)', 'AgentController::view/$1', ['filter' => 'permission:agent.view']);
    $routes->get('agents/edit/(:num)', 'AgentController::edit/$1', ['filter' => 'permission:agent.edit']);
    $routes->post('agents/update/(:num)', 'AgentController::update/$1', ['filter' => 'permission:agent.edit']);
    $routes->get('agents/delete/(:num)', 'AgentController::delete/$1', ['filter' => 'permission:agent.delete']);

    // Transports
    $routes->get('transports', 'TransportController::index', ['filter' => 'permission:transport.view']);
    $routes->get('transports/create', 'TransportController::create', ['filter' => 'permission:transport.create']);
    $routes->post('transports/store', 'TransportController::store', ['filter' => 'permission:transport.create']);
    $routes->get('transports/view/(:num)', 'TransportController::view/$1', ['filter' => 'permission:transport.view']);
    $routes->get('transports/edit/(:num)', 'TransportController::edit/$1', ['filter' => 'permission:transport.edit']);
    $routes->post('transports/update/(:num)', 'TransportController::update/$1', ['filter' => 'permission:transport.edit']);
    $routes->get('transports/delete/(:num)', 'TransportController::delete/$1', ['filter' => 'permission:transport.delete']);

    // User Management
    $routes->get('users', 'UserController::index', ['filter' => 'permission:user.view']);
    $routes->get('users/create', 'UserController::create', ['filter' => 'permission:user.create']);
    $routes->post('users/store', 'UserController::store', ['filter' => 'permission:user.create']);
    $routes->get('users/edit/(:num)', 'UserController::edit/$1', ['filter' => 'permission:user.edit']);
    $routes->post('users/update/(:num)', 'UserController::update/$1', ['filter' => 'permission:user.edit']);
    $routes->get('users/delete/(:num)', 'UserController::delete/$1', ['filter' => 'permission:user.delete']);
    $routes->get('users/convert/(:num)', 'UserController::convertFromEmployee/$1', ['filter' => 'permission:user.create']);
    $routes->post('users/store-conversion', 'UserController::storeFromEmployee', ['filter' => 'permission:user.create']);

    // Role Management
    $routes->get('roles', 'RoleController::index', ['filter' => 'permission:role.view']);
    $routes->get('roles/create', 'RoleController::create', ['filter' => 'permission:role.create']);
    $routes->post('roles/store', 'RoleController::store', ['filter' => 'permission:role.create']);
    $routes->get('roles/edit/(:num)', 'RoleController::edit/$1', ['filter' => 'permission:role.edit']);
    $routes->post('roles/update/(:num)', 'RoleController::update/$1', ['filter' => 'permission:role.edit']);
    $routes->get('roles/delete/(:num)', 'RoleController::delete/$1', ['filter' => 'permission:role.delete']);
    $routes->get('roles/permissions/(:num)', 'RoleController::permissions/$1', ['filter' => 'permission:role.edit']);
    $routes->post('roles/permissions/update/(:num)', 'RoleController::updatePermissions/$1', ['filter' => 'permission:role.edit']);

    // Module Management
    $routes->get('modules', 'ModuleController::index', ['filter' => 'permission:module.view']);
    $routes->get('modules/create', 'ModuleController::create', ['filter' => 'permission:module.create']);
    $routes->post('modules/store', 'ModuleController::store', ['filter' => 'permission:module.create']);
    $routes->get('modules/edit/(:num)', 'ModuleController::edit/$1', ['filter' => 'permission:module.edit']);
    $routes->post('modules/update/(:num)', 'ModuleController::update/$1', ['filter' => 'permission:module.edit']);
    $routes->get('modules/delete/(:num)', 'ModuleController::delete/$1', ['filter' => 'permission:module.delete']);

    // Permission Management
    $routes->get('permissions', 'PermissionController::index', ['filter' => 'permission:permission.view']);
    $routes->get('permissions/create', 'PermissionController::create', ['filter' => 'permission:permission.create']);
    $routes->post('permissions/store', 'PermissionController::store', ['filter' => 'permission:permission.create']);
    $routes->get('permissions/edit/(:num)', 'PermissionController::edit/$1', ['filter' => 'permission:permission.edit']);
    $routes->post('permissions/update/(:num)', 'PermissionController::update/$1', ['filter' => 'permission:permission.edit']);
    $routes->get('permissions/delete/(:num)', 'PermissionController::delete/$1', ['filter' => 'permission:permission.delete']);

    // Employee Management
    $routes->get('employees', 'EmployeeController::index', ['filter' => 'permission:employee.view']);
    $routes->get('employees/create', 'EmployeeController::create', ['filter' => 'permission:employee.create']);
    $routes->post('employees/store', 'EmployeeController::store', ['filter' => 'permission:employee.create']);
    $routes->get('employees/view/(:num)', 'EmployeeController::view/$1', ['filter' => 'permission:employee.view']);
    $routes->get('employees/edit/(:num)', 'EmployeeController::edit/$1', ['filter' => 'permission:employee.edit']);
    $routes->post('employees/update/(:num)', 'EmployeeController::update/$1', ['filter' => 'permission:employee.edit']);
    $routes->get('employees/delete/(:num)', 'EmployeeController::delete/$1', ['filter' => 'permission:employee.delete']);

    // Attendance Management
    $routes->get('attendance', 'AttendanceController::index', ['filter' => 'permission:attendance.view']);
    $routes->post('attendance/store', 'AttendanceController::store', ['filter' => 'permission:attendance.create']);
    $routes->get('attendance/report', 'AttendanceController::report', ['filter' => 'permission:attendance.view']);
    $routes->get('attendance/shortfall', 'AttendanceController::shortfall', ['filter' => 'permission:attendance.view']);

    // Loan Management
    $routes->get('loans', 'LoanController::index', ['filter' => 'permission:loan.view']);
    $routes->get('loans/create', 'LoanController::create', ['filter' => 'permission:loan.create']);
    $routes->post('loans/store', 'LoanController::store', ['filter' => 'permission:loan.create']);
    $routes->get('loans/view/(:num)', 'LoanController::view/$1', ['filter' => 'permission:loan.view']);
    $routes->get('loans/edit/(:num)', 'LoanController::edit/$1', ['filter' => 'permission:loan.edit']);
    $routes->post('loans/update/(:num)', 'LoanController::update/$1', ['filter' => 'permission:loan.edit']);
    $routes->get('loans/delete/(:num)', 'LoanController::delete/$1', ['filter' => 'permission:loan.delete']);

    // Setting Management
    $routes->get('settings', 'SettingController::index', ['filter' => 'permission:setting.view']);
    $routes->post('settings/update', 'SettingController::update', ['filter' => 'permission:setting.edit']);

    // Salary Management
    $routes->get('salaries', 'SalaryController::index', ['filter' => 'permission:salary.view']);
    $routes->get('salaries/calculate', 'SalaryController::calculate', ['filter' => 'permission:salary.create']);
    $routes->post('salaries/process', 'SalaryController::process', ['filter' => 'permission:salary.create']);
    $routes->get('salaries/payslip/(:num)', 'SalaryController::payslip/$1', ['filter' => 'permission:salary.view']);
    $routes->get('salaries/mark-paid/(:num)', 'SalaryController::markPaid/$1', ['filter' => 'permission:salary.edit']);
    $routes->get('salaries/mark-unpaid/(:num)', 'SalaryController::markUnpaid/$1', ['filter' => 'permission:salary.edit']);
    $routes->get('salaries/delete/(:num)', 'SalaryController::delete/$1', ['filter' => 'permission:salary.delete']);

    // Bank Account Management
    $routes->get('bank_accounts', 'BankAccountController::index', ['filter' => 'permission:bank_account.view']);
    $routes->get('bank_accounts/create', 'BankAccountController::create', ['filter' => 'permission:bank_account.create']);
    $routes->post('bank_accounts/store', 'BankAccountController::store', ['filter' => 'permission:bank_account.create']);
    $routes->get('bank_accounts/edit/(:num)', 'BankAccountController::edit/$1', ['filter' => 'permission:bank_account.edit']);
    $routes->post('bank_accounts/update/(:num)', 'BankAccountController::update/$1', ['filter' => 'permission:bank_account.edit']);
    $routes->get('bank_accounts/delete/(:num)', 'BankAccountController::delete/$1', ['filter' => 'permission:bank_account.delete']);

    // Bank Transaction Management (Statements)
    $routes->get('bank_accounts/statement/(:num)', 'BankTransactionController::index/$1', ['filter' => 'permission:bank_account.view']);
    $routes->post('bank_accounts/transactions/store', 'BankTransactionController::store', ['filter' => 'permission:bank_account.edit']);
    $routes->get('bank_accounts/transactions/edit/(:num)', 'BankTransactionController::edit/$1', ['filter' => 'permission:bank_account.edit']);
    $routes->post('bank_accounts/transactions/update/(:num)', 'BankTransactionController::update/$1', ['filter' => 'permission:bank_account.edit']);
    $routes->get('bank_accounts/transactions/delete/(:num)', 'BankTransactionController::delete/$1', ['filter' => 'permission:bank_account.edit']);

    // Bank Reconciliation
    $routes->get('reconciliation', 'ReconciliationController::index', ['filter' => 'permission:bank_account.view']);
    $routes->get('reconciliation/account/(:num)', 'ReconciliationController::account/$1', ['filter' => 'permission:bank_account.view']);
    $routes->post('reconciliation/match', 'ReconciliationController::match', ['filter' => 'permission:bank_account.edit']);
    $routes->post('reconciliation/unmatch', 'ReconciliationController::unmatch', ['filter' => 'permission:bank_account.edit']);
    $routes->get('reconciliation/report/(:num)/(:any)/(:any)', 'ReconciliationController::report/$1/$2/$3', ['filter' => 'permission:bank_account.view']);

    // Calendar Routes
    $routes->group('calendar', function ($routes) {
        $routes->get('/', 'CalendarController::index');
        $routes->get('fetch', 'CalendarController::fetchEvents');
        $routes->post('store', 'CalendarController::store');
        $routes->post('update/(:num)', 'CalendarController::update/$1');
        $routes->post('delete/(:num)', 'CalendarController::delete/$1');
        $routes->get('check', 'CalendarController::checkReminders');
    });

    // Expense Management
    $routes->get('expenses', 'ExpenseController::index', ['filter' => 'permission:expense.view']);
    $routes->get('expenses/create', 'ExpenseController::create', ['filter' => 'permission:expense.create']);
    $routes->post('expenses/store', 'ExpenseController::store', ['filter' => 'permission:expense.create']);
    $routes->get('expenses/edit/(:num)', 'ExpenseController::edit/$1', ['filter' => 'permission:expense.edit']);
    $routes->post('expenses/update/(:num)', 'ExpenseController::update/$1', ['filter' => 'permission:expense.edit']);
    $routes->get('expenses/delete/(:num)', 'ExpenseController::delete/$1', ['filter' => 'permission:expense.delete']);

    // Expense Category Management
    $routes->get('expense_categories', 'ExpenseCategoryController::index', ['filter' => 'permission:expense.view']);
    $routes->get('expense_categories/create', 'ExpenseCategoryController::create', ['filter' => 'permission:expense.create']);
    $routes->post('expense_categories/store', 'ExpenseCategoryController::store', ['filter' => 'permission:expense.create']);
    $routes->get('expense_categories/edit/(:num)', 'ExpenseCategoryController::edit/$1', ['filter' => 'permission:expense.edit']);
    $routes->post('expense_categories/update/(:num)', 'ExpenseCategoryController::update/$1', ['filter' => 'permission:expense.edit']);
    $routes->get('expense_categories/delete/(:num)', 'ExpenseCategoryController::delete/$1', ['filter' => 'permission:expense.delete']);

    // Sales (Customers) Management
    $routes->get('customers', 'CustomerController::index', ['filter' => 'permission:customer.view']);
    $routes->get('customers/datatable', 'CustomerController::datatable', ['filter' => 'permission:customer.view']);
    $routes->get('customers/create', 'CustomerController::create', ['filter' => 'permission:customer.create']);
    $routes->post('customers/store', 'CustomerController::store', ['filter' => 'permission:customer.create']);
    $routes->get('customers/view/(:num)', 'CustomerController::view/$1', ['filter' => 'permission:customer.view']);
    $routes->get('customers/edit/(:num)', 'CustomerController::edit/$1', ['filter' => 'permission:customer.edit']);
    $routes->post('customers/update/(:num)', 'CustomerController::update/$1', ['filter' => 'permission:customer.edit']);
    $routes->get('customers/delete/(:num)', 'CustomerController::delete/$1', ['filter' => 'permission:customer.delete']);
    $routes->get('customers/statement/(:num)', 'CustomerController::statement/$1', ['filter' => 'permission:customer.view']);
    $routes->get('customers/details/(:num)', 'CustomerController::getDetails/$1');

    // Sales Returns
    $routes->get('sales_returns', 'SalesReturnController::index', ['filter' => 'permission:invoice.view']);
    $routes->get('sales_returns/create', 'SalesReturnController::create', ['filter' => 'permission:invoice.edit']);
    $routes->post('sales_returns/store', 'SalesReturnController::store', ['filter' => 'permission:invoice.edit']);
    $routes->get('sales_returns/view/(:num)', 'SalesReturnController::view/$1', ['filter' => 'permission:invoice.view']);
    $routes->get('sales_returns/sync-zoho', 'SalesReturnController::syncFromZoho', ['filter' => 'permission:zoho.sync']);

    // Purchase (Vendors) Management
    $routes->get('vendors', 'VendorController::index', ['filter' => 'permission:vendor.view']);
    $routes->get('vendors/returns', 'VendorController::pendingReturns', ['filter' => 'permission:vendor.view']);
    $routes->get('vendors/returns/create', 'VendorController::createReturnBatch', ['filter' => 'permission:vendor.edit']);
    $routes->post('vendors/returns/scan', 'VendorController::scanReturnItem', ['filter' => 'permission:vendor.edit']);
    $routes->post('vendors/returns/process', 'VendorController::processReturnBatch', ['filter' => 'permission:vendor.edit']);
    $routes->get('vendors/create', 'VendorController::create', ['filter' => 'permission:vendor.create']);
    $routes->post('vendors/store', 'VendorController::store', ['filter' => 'permission:vendor.create']);
    $routes->get('vendors/details/(:num)', 'VendorController::getDetails/$1', ['filter' => 'permission:vendor.view']);
    $routes->get('vendors/view/(:num)', 'VendorController::view/$1', ['filter' => 'permission:vendor.view']);
    $routes->get('vendors/edit/(:num)', 'VendorController::edit/$1', ['filter' => 'permission:vendor.edit']);
    $routes->post('vendors/update/(:num)', 'VendorController::update/$1', ['filter' => 'permission:vendor.edit']);
    $routes->get('vendors/delete/(:num)', 'VendorController::delete/$1', ['filter' => 'permission:vendor.delete']);
    $routes->get('vendors/statement/(:num)', 'VendorController::statement/$1', ['filter' => 'permission:vendor.view']);
    $routes->post('vendors/processReturn/(:num)', 'VendorController::processReturn/$1', ['filter' => 'permission:vendor.edit']);

    // Return Shipments
    $routes->get('vendors/returns/shipments', 'VendorController::listShipments', ['filter' => 'permission:vendor.view']);
    $routes->post('vendors/returns/shipments/update/(:num)', 'VendorController::updateShipment/$1', ['filter' => 'permission:vendor.edit']);
    $routes->post('vendors/returns/shipments/update-status/(:num)', 'VendorController::updateShipmentStatus/$1', ['filter' => 'permission:vendor.edit']);

    // Inventory (Categories)
    $routes->get('product_categories', 'ProductCategoryController::index', ['filter' => 'permission:product_category.view']);
    $routes->post('product_categories/store', 'ProductCategoryController::store', ['filter' => 'permission:product_category.create']);
    $routes->get('product_categories/edit/(:num)', 'ProductCategoryController::edit/$1', ['filter' => 'permission:product_category.edit']);
    $routes->post('product_categories/update/(:num)', 'ProductCategoryController::update/$1', ['filter' => 'permission:product_category.edit']);
    $routes->get('product_categories/delete/(:num)', 'ProductCategoryController::delete/$1', ['filter' => 'permission:product_category.delete']);

    // Inventory (Products)
    $routes->get('products', 'ProductController::index', ['filter' => 'permission:product.view']);
    $routes->get('products/approvals', 'ProductController::approvals', ['filter' => 'permission:product.edit']);
    $routes->get('products/create', 'ProductController::create', ['filter' => 'permission:product.create']);
    $routes->post('products/store', 'ProductController::store', ['filter' => 'permission:product.create']);
    $routes->get('products/view/(:num)', 'ProductController::view/$1', ['filter' => 'permission:product.view']);
    $routes->get('products/edit/(:num)', 'ProductController::edit/$1', ['filter' => 'permission:product.edit']);
    $routes->post('products/update/(:num)', 'ProductController::update/$1', ['filter' => 'permission:product.edit']);
    $routes->get('products/delete/(:num)', 'ProductController::delete/$1', ['filter' => 'permission:product.delete']);
    $routes->post('products/addItem/(:num)', 'ProductController::addItem/$1', ['filter' => 'permission:product.create']);
    $routes->post('products/approveItem/(:num)', 'ProductController::approveItem/$1', ['filter' => 'permission:product.edit']);
    $routes->post('products/rejectItem/(:num)', 'ProductController::rejectItem/$1', ['filter' => 'permission:product.edit']);
    $routes->post('products/markDamaged/(:num)', 'ProductController::markDamaged/$1', ['filter' => 'permission:product.edit']);
    $routes->get('products/barcode/(:segment)', 'ProductController::getByBarcode/$1');

    // Production (Weavers)
    $routes->get('production/weavers', 'WeaverController::index', ['filter' => 'permission:weaver.view']);
    $routes->get('production/weavers/create', 'WeaverController::create', ['filter' => 'permission:weaver.create']);
    $routes->post('production/weavers/store', 'WeaverController::store', ['filter' => 'permission:weaver.create']);
    $routes->get('production/weavers/edit/(:num)', 'WeaverController::edit/$1', ['filter' => 'permission:weaver.edit']);
    $routes->post('production/weavers/update/(:num)', 'WeaverController::update/$1', ['filter' => 'permission:weaver.edit']);
    $routes->get('production/weavers/delete/(:num)', 'WeaverController::delete/$1', ['filter' => 'permission:weaver.delete']);

    // Bills
    $routes->get('bills', 'BillController::index', ['filter' => 'permission:bill.view']);
    $routes->get('bills/create', 'BillController::create', ['filter' => 'permission:bill.create']);
    $routes->post('bills/store', 'BillController::store', ['filter' => 'permission:bill.create']);
    $routes->get('bills/view/(:num)', 'BillController::view/$1', ['filter' => 'permission:bill.view']);
    $routes->get('bills/edit/(:num)', 'BillController::edit/$1', ['filter' => 'permission:bill.edit']);
    $routes->post('bills/update/(:num)', 'BillController::update/$1', ['filter' => 'permission:bill.edit']);
    $routes->get('bills/mark-open/(:num)', 'BillController::markAsOpen/$1', ['filter' => 'permission:bill.edit']);
    $routes->post('bills/void/(:num)', 'BillController::delete/$1', ['filter' => 'permission:bill.delete']);
    $routes->get('bills/payment/(:num)', 'BillController::recordPayment/$1', ['filter' => 'permission:bill.edit']);
    $routes->post('bills/payment/(:num)', 'BillController::storePayment/$1', ['filter' => 'permission:bill.edit']);
    $routes->get('bills/sync-zoho', 'BillController::syncFromZoho', ['filter' => 'permission:zoho.sync']);
    $routes->get('bills/print/(:num)', 'BillController::print/$1', ['filter' => 'permission:bill.view']);

    // Quotations
    $routes->get('quotations', 'QuotationController::index', ['filter' => 'permission:quotation.view']);
    $routes->get('quotations/create', 'QuotationController::create', ['filter' => 'permission:quotation.create']);
    $routes->post('quotations/store', 'QuotationController::store', ['filter' => 'permission:quotation.create']);
    $routes->get('quotations/view/(:num)', 'QuotationController::view/$1', ['filter' => 'permission:quotation.view']);
    $routes->get('quotations/edit/(:num)', 'QuotationController::edit/$1', ['filter' => 'permission:quotation.edit']);
    $routes->post('quotations/update/(:num)', 'QuotationController::update/$1', ['filter' => 'permission:quotation.edit']);
    $routes->get('quotations/delete/(:num)', 'QuotationController::delete/$1', ['filter' => 'permission:quotation.delete']);
    $routes->get('quotations/print/(:num)', 'QuotationController::print/$1', ['filter' => 'permission:quotation.view']);

    // Sales Orders
    $routes->get('sales_orders', 'SalesOrderController::index', ['filter' => 'permission:sales_order.view']);
    $routes->get('sales_orders/create', 'SalesOrderController::create', ['filter' => 'permission:sales_order.create']);
    $routes->post('sales_orders/store', 'SalesOrderController::store', ['filter' => 'permission:sales_order.create']);
    $routes->get('sales_orders/view/(:num)', 'SalesOrderController::view/$1', ['filter' => 'permission:sales_order.view']);
    $routes->get('sales_orders/edit/(:num)', 'SalesOrderController::edit/$1', ['filter' => 'permission:sales_order.edit']);
    $routes->post('sales_orders/update/(:num)', 'SalesOrderController::update/$1', ['filter' => 'permission:sales_order.edit']);
    $routes->get('sales_orders/delete/(:num)', 'SalesOrderController::delete/$1', ['filter' => 'permission:sales_order.delete']);
    $routes->get('sales_orders/print/(:num)', 'SalesOrderController::print/$1', ['filter' => 'permission:sales_order.view']);

    // Invoices
    $routes->get('invoices', 'InvoiceController::index', ['filter' => 'permission:invoice.view']);
    $routes->get('invoices/create', 'InvoiceController::create', ['filter' => 'permission:invoice.create']);
    $routes->post('invoices/store', 'InvoiceController::store', ['filter' => 'permission:invoice.create']);
    $routes->get('invoices/view/(:num)', 'InvoiceController::view/$1', ['filter' => 'permission:invoice.view']);
    $routes->get('invoices/edit/(:num)', 'InvoiceController::edit/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoices/update/(:num)', 'InvoiceController::update/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoices/delete/(:num)', 'InvoiceController::delete/$1', ['filter' => 'permission:invoice.delete']);
    $routes->get('invoices/payment/(:num)', 'InvoiceController::recordPayment/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoices/payment/(:num)', 'InvoiceController::storePayment/$1', ['filter' => 'permission:invoice.edit']);
    $routes->get('invoices/print/(:num)', 'InvoiceController::print/$1', ['filter' => 'permission:invoice.view']);
    $routes->get('invoices/mark-sent/(:num)', 'InvoiceController::markAsSent/$1');
    $routes->get('invoices/tracking', 'InvoiceController::tracking', ['filter' => 'permission:invoice.view']);
    $routes->post('invoices/update-waybill/(:num)', 'InvoiceController::updateWaybill/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoices/update-delivery-status/(:num)', 'InvoiceController::updateDeliveryStatus/$1', ['filter' => 'permission:invoice.edit']);
    $routes->get('invoices/doc-tracking', 'InvoiceController::docTracking', ['filter' => 'permission:invoice.view']);
    $routes->post('invoices/update-doc-details/(:num)', 'InvoiceController::updateDocDetails/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoices/update-doc-status/(:num)', 'InvoiceController::updateDocStatus/$1', ['filter' => 'permission:invoice.edit']);
    $routes->get('invoices/history/(:num)', 'InvoiceController::getHistory/$1');

    // Customer Receipts (Invoice Payments)
    $routes->get('invoice_payments', 'InvoicePaymentController::index', ['filter' => 'permission:invoice.view']);
    $routes->get('invoice_payments/view/(:num)', 'InvoicePaymentController::view/$1', ['filter' => 'permission:invoice.view']);
    $routes->get('invoice_payments/edit/(:num)', 'InvoicePaymentController::edit/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoice_payments/update/(:num)', 'InvoicePaymentController::update/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoice_payments/delete/(:num)', 'InvoicePaymentController::delete/$1', ['filter' => 'permission:invoice.edit']);

    // Vendor Payments
    $routes->get('payments', 'PaymentController::index', ['filter' => 'permission:bill.view']);
    $routes->get('payments/view/(:num)', 'PaymentController::view/$1', ['filter' => 'permission:bill.view']);
    $routes->get('payments/edit/(:num)', 'PaymentController::edit/$1', ['filter' => 'permission:bill.edit']);
    $routes->post('payments/update/(:num)', 'PaymentController::update/$1', ['filter' => 'permission:bill.edit']);
    $routes->post('payments/delete/(:num)', 'PaymentController::delete/$1', ['filter' => 'permission:bill.edit']);

    // Agent Payments
    $routes->get('agent-payments', 'AgentPaymentController::index', ['filter' => 'permission:agent_payments.view']);
    $routes->get('agent-payments/datatable', 'AgentPaymentController::datatable', ['filter' => 'permission:agent_payments.view']);
    $routes->get('agent-payments/create', 'AgentPaymentController::create', ['filter' => 'permission:agent_payments.create']);
    $routes->get('agent-payments/create/(:num)', 'AgentPaymentController::create/$1', ['filter' => 'permission:agent_payments.create']);
    $routes->post('agent-payments/store', 'AgentPaymentController::store', ['filter' => 'permission:agent_payments.create']);
    $routes->get('agent-payments/view/(:num)', 'AgentPaymentController::view/$1', ['filter' => 'permission:agent_payments.view']);
    $routes->get('agent-payments/delete/(:num)', 'AgentPaymentController::delete/$1', ['filter' => 'permission:agent_payments.delete']);
    $routes->get('agent-payments/pending-commissions/(:num)', 'AgentPaymentController::getPendingCommissions/$1', ['filter' => 'permission:agent_payments.view']);
    $routes->get('agent-payments/reports', 'AgentPaymentController::reports', ['filter' => 'permission:agent_payments.reports']);
    $routes->get('agent-payments/preview-report', 'AgentPaymentController::previewReport', ['filter' => 'permission:agent_payments.view']);

    // Master Data AJAX
    $routes->get('master-data/states/(:num)', 'MasterDataController::getStatesByCountry/$1');
    $routes->get('master-data/product-by-barcode', 'MasterDataController::getProductByBarcode');

    // API Routes for n8n Integration
    $routes->group('api', ['filter' => 'apiauth'], function ($routes) {
        // Customer APIs
        $routes->get('customers', 'ApiController::getCustomers');
        $routes->get('customers/(:num)', 'ApiController::getCustomer/$1');
        $routes->put('customers/(:num)', 'ApiController::updateCustomer/$1');
        $routes->post('customers/(:num)/zoho-link', 'ApiController::linkCustomerToZoho/$1');
        $routes->post('customers/(:num)/address/sync', 'ApiController::syncCustomerAddress/$1');

        // Vendor APIs
        $routes->get('vendors', 'ApiController::getVendors');
        $routes->get('vendors/(:num)', 'ApiController::getVendor/$1');
        $routes->put('vendors/(:num)', 'ApiController::updateVendor/$1');
        $routes->post('vendors/(:num)/zoho-link', 'ApiController::linkVendorToZoho/$1');
        $routes->post('vendors/(:num)/address/sync', 'ApiController::syncVendorAddress/$1');
    });

    // Tax Routes
    $routes->group('settings/taxes', function ($routes) {
        $routes->get('', 'TaxController::index', ['filter' => 'permission:tax.view']);
        $routes->get('new', 'TaxController::new', ['filter' => 'permission:tax.create']);
        $routes->post('delete/(:num)', 'CalendarController::delete/$1');
        $routes->get('check', 'CalendarController::checkReminders');
    });

    // Expense Management
    $routes->get('expenses', 'ExpenseController::index', ['filter' => 'permission:expense.view']);
    $routes->get('expenses/create', 'ExpenseController::create', ['filter' => 'permission:expense.create']);
    $routes->post('expenses/store', 'ExpenseController::store', ['filter' => 'permission:expense.create']);
    $routes->get('expenses/edit/(:num)', 'ExpenseController::edit/$1', ['filter' => 'permission:expense.edit']);
    $routes->post('expenses/update/(:num)', 'ExpenseController::update/$1', ['filter' => 'permission:expense.edit']);
    $routes->get('expenses/delete/(:num)', 'ExpenseController::delete/$1', ['filter' => 'permission:expense.delete']);

    // Expense Category Management
    $routes->get('expense_categories', 'ExpenseCategoryController::index', ['filter' => 'permission:expense.view']);
    $routes->get('expense_categories/create', 'ExpenseCategoryController::create', ['filter' => 'permission:expense.create']);
    $routes->post('expense_categories/store', 'ExpenseCategoryController::store', ['filter' => 'permission:expense.create']);
    $routes->get('expense_categories/edit/(:num)', 'ExpenseCategoryController::edit/$1', ['filter' => 'permission:expense.edit']);
    $routes->post('expense_categories/update/(:num)', 'ExpenseCategoryController::update/$1', ['filter' => 'permission:expense.edit']);
    $routes->get('expense_categories/delete/(:num)', 'ExpenseCategoryController::delete/$1', ['filter' => 'permission:expense.delete']);

    // Sales (Customers) Management
    $routes->get('customers', 'CustomerController::index', ['filter' => 'permission:customer.view']);
    $routes->get('customers/datatable', 'CustomerController::datatable', ['filter' => 'permission:customer.view']);
    $routes->get('customers/create', 'CustomerController::create', ['filter' => 'permission:customer.create']);
    $routes->post('customers/store', 'CustomerController::store', ['filter' => 'permission:customer.create']);
    $routes->get('customers/view/(:num)', 'CustomerController::view/$1', ['filter' => 'permission:customer.view']);
    $routes->get('customers/edit/(:num)', 'CustomerController::edit/$1', ['filter' => 'permission:customer.edit']);
    $routes->post('customers/update/(:num)', 'CustomerController::update/$1', ['filter' => 'permission:customer.edit']);
    $routes->get('customers/delete/(:num)', 'CustomerController::delete/$1', ['filter' => 'permission:customer.delete']);
    $routes->get('customers/statement/(:num)', 'CustomerController::statement/$1', ['filter' => 'permission:customer.view']);
    $routes->get('customers/details/(:num)', 'CustomerController::getDetails/$1');

    // Sales Returns
    $routes->get('sales_returns', 'SalesReturnController::index', ['filter' => 'permission:invoice.view']);
    $routes->get('sales_returns/create', 'SalesReturnController::create', ['filter' => 'permission:invoice.edit']);
    $routes->post('sales_returns/store', 'SalesReturnController::store', ['filter' => 'permission:invoice.edit']);
    $routes->get('sales_returns/view/(:num)', 'SalesReturnController::view/$1', ['filter' => 'permission:invoice.view']);
    $routes->get('sales_returns/sync-zoho', 'SalesReturnController::syncFromZoho', ['filter' => 'permission:zoho.sync']);

    // Purchase (Vendors) Management
    $routes->get('vendors', 'VendorController::index', ['filter' => 'permission:vendor.view']);
    $routes->get('vendors/returns', 'VendorController::pendingReturns', ['filter' => 'permission:vendor.view']);
    $routes->get('vendors/returns/create', 'VendorController::createReturnBatch', ['filter' => 'permission:vendor.edit']);
    $routes->post('vendors/returns/scan', 'VendorController::scanReturnItem', ['filter' => 'permission:vendor.edit']);
    $routes->post('vendors/returns/process', 'VendorController::processReturnBatch', ['filter' => 'permission:vendor.edit']);
    $routes->get('vendors/create', 'VendorController::create', ['filter' => 'permission:vendor.create']);
    $routes->post('vendors/store', 'VendorController::store', ['filter' => 'permission:vendor.create']);
    $routes->get('vendors/details/(:num)', 'VendorController::getDetails/$1', ['filter' => 'permission:vendor.view']);
    $routes->get('vendors/view/(:num)', 'VendorController::view/$1', ['filter' => 'permission:vendor.view']);
    $routes->get('vendors/edit/(:num)', 'VendorController::edit/$1', ['filter' => 'permission:vendor.edit']);
    $routes->post('vendors/update/(:num)', 'VendorController::update/$1', ['filter' => 'permission:vendor.edit']);
    $routes->get('vendors/delete/(:num)', 'VendorController::delete/$1', ['filter' => 'permission:vendor.delete']);
    $routes->get('vendors/statement/(:num)', 'VendorController::statement/$1', ['filter' => 'permission:vendor.view']);
    $routes->post('vendors/processReturn/(:num)', 'VendorController::processReturn/$1', ['filter' => 'permission:vendor.edit']);

    // Return Shipments
    $routes->get('vendors/returns/shipments', 'VendorController::listShipments', ['filter' => 'permission:vendor.view']);
    $routes->post('vendors/returns/shipments/update/(:num)', 'VendorController::updateShipment/$1', ['filter' => 'permission:vendor.edit']);
    $routes->post('vendors/returns/shipments/update-status/(:num)', 'VendorController::updateShipmentStatus/$1', ['filter' => 'permission:vendor.edit']);

    // Inventory (Categories)
    $routes->get('product_categories', 'ProductCategoryController::index', ['filter' => 'permission:product_category.view']);
    $routes->post('product_categories/store', 'ProductCategoryController::store', ['filter' => 'permission:product_category.create']);
    $routes->get('product_categories/edit/(:num)', 'ProductCategoryController::edit/$1', ['filter' => 'permission:product_category.edit']);
    $routes->post('product_categories/update/(:num)', 'ProductCategoryController::update/$1', ['filter' => 'permission:product_category.edit']);
    $routes->get('product_categories/delete/(:num)', 'ProductCategoryController::delete/$1', ['filter' => 'permission:product_category.delete']);

    // Inventory (Products)
    $routes->get('products', 'ProductController::index', ['filter' => 'permission:product.view']);
    $routes->get('products/approvals', 'ProductController::approvals', ['filter' => 'permission:product.edit']);
    $routes->get('products/create', 'ProductController::create', ['filter' => 'permission:product.create']);
    $routes->post('products/store', 'ProductController::store', ['filter' => 'permission:product.create']);
    $routes->get('products/view/(:num)', 'ProductController::view/$1', ['filter' => 'permission:product.view']);
    $routes->get('products/edit/(:num)', 'ProductController::edit/$1', ['filter' => 'permission:product.edit']);
    $routes->post('products/update/(:num)', 'ProductController::update/$1', ['filter' => 'permission:product.edit']);
    $routes->get('products/delete/(:num)', 'ProductController::delete/$1', ['filter' => 'permission:product.delete']);
    $routes->post('products/addItem/(:num)', 'ProductController::addItem/$1', ['filter' => 'permission:product.create']);
    $routes->post('products/approveItem/(:num)', 'ProductController::approveItem/$1', ['filter' => 'permission:product.edit']);
    $routes->post('products/rejectItem/(:num)', 'ProductController::rejectItem/$1', ['filter' => 'permission:product.edit']);
    $routes->post('products/markDamaged/(:num)', 'ProductController::markDamaged/$1', ['filter' => 'permission:product.edit']);
    $routes->get('products/barcode/(:segment)', 'ProductController::getByBarcode/$1');

    // Production (Weavers)
    $routes->get('production/weavers', 'WeaverController::index', ['filter' => 'permission:weaver.view']);
    $routes->get('production/weavers/view/(:num)', 'WeaverController::view/$1', ['filter' => 'permission:weaver.view']);
    $routes->get('production/weavers/create', 'WeaverController::create', ['filter' => 'permission:weaver.create']);
    $routes->post('production/weavers/store', 'WeaverController::store', ['filter' => 'permission:weaver.create']);
    $routes->get('production/weavers/edit/(:num)', 'WeaverController::edit/$1', ['filter' => 'permission:weaver.edit']);
    $routes->post('production/weavers/update/(:num)', 'WeaverController::update/$1', ['filter' => 'permission:weaver.edit']);
    $routes->get('production/weavers/delete/(:num)', 'WeaverController::delete/$1', ['filter' => 'permission:weaver.delete']);

    // Production (Job Work Vendors)
    $routes->get('production/vendors', 'ProductionVendorController::index', ['filter' => 'permission:production_vendor.view']);
    $routes->get('production/vendors/view/(:num)', 'ProductionVendorController::view/$1', ['filter' => 'permission:production_vendor.view']);
    $routes->get('production/vendors/create', 'ProductionVendorController::create', ['filter' => 'permission:production_vendor.create']);
    $routes->post('production/vendors/store', 'ProductionVendorController::store', ['filter' => 'permission:production_vendor.create']);
    $routes->get('production/vendors/edit/(:num)', 'ProductionVendorController::edit/$1', ['filter' => 'permission:production_vendor.edit']);
    $routes->post('production/vendors/update/(:num)', 'ProductionVendorController::update/$1', ['filter' => 'permission:production_vendor.edit']);
    $routes->get('production/vendors/delete/(:num)', 'ProductionVendorController::delete/$1', ['filter' => 'permission:production_vendor.delete']);

    // Production (Agreements)
    $routes->get('production/agreements', 'ProductionAgreementController::index', ['filter' => 'permission:production_agreement.view']);
    $routes->get('production/agreements/view/(:num)', 'ProductionAgreementController::view/$1', ['filter' => 'permission:production_agreement.view']);
    $routes->get('production/agreements/create', 'ProductionAgreementController::create', ['filter' => 'permission:production_agreement.create']);
    $routes->post('production/agreements/store', 'ProductionAgreementController::store', ['filter' => 'permission:production_agreement.create']);
    $routes->get('production/agreements/edit/(:num)', 'ProductionAgreementController::edit/$1', ['filter' => 'permission:production_agreement.edit']);
    $routes->post('production/agreements/update/(:num)', 'ProductionAgreementController::update/$1', ['filter' => 'permission:production_agreement.edit']);
    $routes->get('production/agreements/delete/(:num)', 'ProductionAgreementController::delete/$1', ['filter' => 'permission:production_agreement.delete']);

    // Production (Yarn Master)
    $routes->get('production/yarns/master', 'ProductionYarnController::masterIndex', ['filter' => 'permission:yarn_master.view']);
    $routes->get('production/yarns/master/create', 'ProductionYarnController::masterCreate', ['filter' => 'permission:yarn_master.create']);
    $routes->post('production/yarns/master/store', 'ProductionYarnController::masterStore', ['filter' => 'permission:yarn_master.create']);
    $routes->get('production/yarns/master/edit/(:num)', 'ProductionYarnController::masterEdit/$1', ['filter' => 'permission:yarn_master.edit']);
    $routes->post('production/yarns/master/update/(:num)', 'ProductionYarnController::masterUpdate/$1', ['filter' => 'permission:yarn_master.edit']);
    $routes->get('production/yarns/master/delete/(:num)', 'ProductionYarnController::masterDelete/$1', ['filter' => 'permission:yarn_master.delete']);

    // Production (Colors CRUD)
    $routes->get('production/colors', 'ColorController::index', ['filter' => 'permission:color.view']);
    $routes->get('production/colors/create', 'ColorController::create', ['filter' => 'permission:color.create']);
    $routes->post('production/colors/store', 'ColorController::store', ['filter' => 'permission:color.create']);
    $routes->get('production/colors/edit/(:num)', 'ColorController::edit/$1', ['filter' => 'permission:color.edit']);
    $routes->post('production/colors/update/(:num)', 'ColorController::update/$1', ['filter' => 'permission:color.edit']);
    $routes->get('production/colors/delete/(:num)', 'ColorController::delete/$1', ['filter' => 'permission:color.delete']);

    // Production (Yarns)
    $routes->get('production/yarns', 'ProductionYarnController::index', ['filter' => 'permission:yarn.view']);
    $routes->get('production/yarns/view/(:num)', 'ProductionYarnController::view/$1', ['filter' => 'permission:yarn.view']);

    // Production (Yarn Purchases)
    $routes->get('production/yarn-purchases', 'YarnPurchaseController::index', ['filter' => 'permission:yarn_purchase.view']);
    $routes->get('production/yarn-purchases/view/(:num)', 'YarnPurchaseController::view/$1', ['filter' => 'permission:yarn_purchase.view']);
    $routes->get('production/yarn-purchases/create', 'YarnPurchaseController::create', ['filter' => 'permission:yarn_purchase.create']);
    $routes->post('production/yarn-purchases/store', 'YarnPurchaseController::store', ['filter' => 'permission:yarn_purchase.create']);
    $routes->get('production/yarn-purchases/edit/(:num)', 'YarnPurchaseController::edit/$1', ['filter' => 'permission:yarn_purchase.edit']);
    $routes->post('production/yarn-purchases/update/(:num)', 'YarnPurchaseController::update/$1', ['filter' => 'permission:yarn_purchase.edit']);
    $routes->get('production/yarn-purchases/delete/(:num)', 'YarnPurchaseController::delete/$1', ['filter' => 'permission:yarn_purchase.delete']);

    // Production (Yarn Inventory)
    $routes->get('production/yarn-inventory', 'YarnInventoryController::index', ['filter' => 'permission:yarn_inventory.view']);
    $routes->get('production/yarn-inventory/history', 'YarnInventoryController::history', ['filter' => 'permission:yarn_inventory.view']);
    $routes->get('production/yarn-job-work-all-dcs', 'YarnBeamController::allDcs', ['filter' => 'permission:yarn_inventory.view']);



    // Production (Yarn Dyeing)
    $routes->get('production/yarn-dyeing', 'YarnDyeingController::index', ['filter' => 'permission:yarn_dyeing.view']);
    $routes->get('production/yarn-dyeing/create', 'YarnDyeingController::create', ['filter' => 'permission:yarn_dyeing.create']);
    $routes->post('production/yarn-dyeing/store', 'YarnDyeingController::store', ['filter' => 'permission:yarn_dyeing.create']);
    $routes->get('production/yarn-dyeing/view/(:num)', 'YarnDyeingController::view/$1', ['filter' => 'permission:yarn_dyeing.view']);
    $routes->get('production/yarn-dyeing/edit/(:num)', 'YarnDyeingController::edit/$1', ['filter' => 'permission:yarn_dyeing.edit']);
    $routes->post('production/yarn-dyeing/update/(:num)', 'YarnDyeingController::update/$1', ['filter' => 'permission:yarn_dyeing.edit']);
    $routes->get('production/yarn-dyeing/delete/(:num)', 'YarnDyeingController::delete/$1', ['filter' => 'permission:yarn_dyeing.delete']);
    $routes->get('production/yarn-dyeing/receipt-create/(:num)', 'YarnDyeingController::receiptCreate/$1', ['filter' => 'permission:yarn_dyeing.create']);
    $routes->post('production/yarn-dyeing/receipt-store/(:num)', 'YarnDyeingController::receiptStore/$1', ['filter' => 'permission:yarn_dyeing.create']);
    $routes->get('production/yarn-dyeing/receipt-view/(:num)', 'YarnDyeingController::receiptView/$1', ['filter' => 'permission:yarn_dyeing.view']);
    $routes->get('production/yarn-dyeing/receipt-edit/(:num)', 'YarnDyeingController::receiptEdit/$1', ['filter' => 'permission:yarn_dyeing.edit']);
    $routes->post('production/yarn-dyeing/receipt-update/(:num)', 'YarnDyeingController::receiptUpdate/$1', ['filter' => 'permission:yarn_dyeing.edit']);
    $routes->get('production/yarn-dyeing/receipt-delete/(:num)', 'YarnDyeingController::receiptDelete/$1', ['filter' => 'permission:yarn_dyeing.delete']);

    // Production (Yarn Warping & Sizing)
    $routes->get('production/yarn-warping-sizing', 'YarnWarpingSizingController::index', ['filter' => 'permission:yarn_warping_sizing.view']);
    $routes->get('production/yarn-warping-sizing/create', 'YarnWarpingSizingController::create', ['filter' => 'permission:yarn_warping_sizing.create']);
    $routes->post('production/yarn-warping-sizing/store', 'YarnWarpingSizingController::store', ['filter' => 'permission:yarn_warping_sizing.create']);
    $routes->get('production/yarn-warping-sizing/view/(:num)', 'YarnWarpingSizingController::view/$1', ['filter' => 'permission:yarn_warping_sizing.view']);
    $routes->get('production/yarn-warping-sizing/edit/(:num)', 'YarnWarpingSizingController::edit/$1', ['filter' => 'permission:yarn_warping_sizing.edit']);
    $routes->post('production/yarn-warping-sizing/update/(:num)', 'YarnWarpingSizingController::update/$1', ['filter' => 'permission:yarn_warping_sizing.edit']);
    $routes->get('production/yarn-warping-sizing/delete/(:num)', 'YarnWarpingSizingController::delete/$1', ['filter' => 'permission:yarn_warping_sizing.delete']);
    $routes->get('production/yarn-warping-sizing/receipt-create/(:num)', 'YarnWarpingSizingController::receiptCreate/$1', ['filter' => 'permission:yarn_warping_sizing.create']);
    $routes->post('production/yarn-warping-sizing/receipt-store/(:num)', 'YarnWarpingSizingController::receiptStore/$1', ['filter' => 'permission:yarn_warping_sizing.create']);
    $routes->get('production/yarn-warping-sizing/receipt-edit/(:num)', 'YarnWarpingSizingController::receiptEdit/$1', ['filter' => 'permission:yarn_warping_sizing.edit']);
    $routes->post('production/yarn-warping-sizing/receipt-update/(:num)', 'YarnWarpingSizingController::receiptUpdate/$1', ['filter' => 'permission:yarn_warping_sizing.edit']);
    $routes->get('production/yarn-warping-sizing/receipt-view/(:num)', 'YarnWarpingSizingController::receiptView/$1', ['filter' => 'permission:yarn_warping_sizing.view']);
    $routes->get('production/yarn-warping-sizing/receipt-delete/(:num)', 'YarnWarpingSizingController::receiptDelete/$1', ['filter' => 'permission:yarn_warping_sizing.delete']);

    // Production (Yarn Twisting)
    $routes->get('production/yarn-twisting', 'YarnTwistingController::index', ['filter' => 'permission:yarn_twisting.view']);
    $routes->get('production/yarn-twisting/create', 'YarnTwistingController::create', ['filter' => 'permission:yarn_twisting.create']);
    $routes->post('production/yarn-twisting/store', 'YarnTwistingController::store', ['filter' => 'permission:yarn_twisting.create']);
    $routes->get('production/yarn-twisting/view/(:num)', 'YarnTwistingController::view/$1', ['filter' => 'permission:yarn_twisting.view']);
    $routes->get('production/yarn-twisting/edit/(:num)', 'YarnTwistingController::edit/$1', ['filter' => 'permission:yarn_twisting.edit']);
    $routes->post('production/yarn-twisting/update/(:num)', 'YarnTwistingController::update/$1', ['filter' => 'permission:yarn_twisting.edit']);
    $routes->get('production/yarn-twisting/delete/(:num)', 'YarnTwistingController::delete/$1', ['filter' => 'permission:yarn_twisting.delete']);
    $routes->get('production/yarn-twisting/receipt-create/(:num)', 'YarnTwistingController::receiptCreate/$1', ['filter' => 'permission:yarn_twisting.create']);
    $routes->post('production/yarn-twisting/receipt-store/(:num)', 'YarnTwistingController::receiptStore/$1', ['filter' => 'permission:yarn_twisting.create']);
    $routes->get('production/yarn-twisting/receipt-delete/(:num)', 'YarnTwistingController::receiptDelete/$1', ['filter' => 'permission:yarn_twisting.delete']);

    // Production (Yarn Weaving)
    $routes->get('production/yarn-weaving', 'YarnWeavingController::index', ['filter' => 'permission:yarn_weaving.view']);
    $routes->get('production/yarn-weaving/create', 'YarnWeavingController::create', ['filter' => 'permission:yarn_weaving.create']);
    $routes->post('production/yarn-weaving/store', 'YarnWeavingController::store', ['filter' => 'permission:yarn_weaving.create']);
    $routes->get('production/yarn-weaving/view/(:num)', 'YarnWeavingController::view/$1', ['filter' => 'permission:yarn_weaving.view']);
    $routes->get('production/yarn-weaving/edit/(:num)', 'YarnWeavingController::edit/$1', ['filter' => 'permission:yarn_weaving.edit']);
    $routes->post('production/yarn-weaving/update/(:num)', 'YarnWeavingController::update/$1', ['filter' => 'permission:yarn_weaving.edit']);
    $routes->get('production/yarn-weaving/delete/(:num)', 'YarnWeavingController::delete/$1', ['filter' => 'permission:yarn_weaving.delete']);
    $routes->get('production/yarn-weaving/receipt-create/(:num)', 'YarnWeavingController::receiptCreate/$1', ['filter' => 'permission:yarn_weaving.create']);
    $routes->post('production/yarn-weaving/receipt-store/(:num)', 'YarnWeavingController::receiptStore/$1', ['filter' => 'permission:yarn_weaving.create']);
    $routes->get('production/yarn-weaving/receipt-delete/(:num)', 'YarnWeavingController::receiptDelete/$1', ['filter' => 'permission:yarn_weaving.delete']);

    // Production (Yarn Beams)
    $routes->get('production/yarn-beams', 'YarnBeamController::index', ['filter' => 'permission:yarn_beam.view']);
    $routes->post('production/yarn-beams/store', 'YarnBeamController::store', ['filter' => 'permission:yarn_beam.create']);
    $routes->get('production/yarn-beams/ledger/(:num)', 'YarnBeamController::ledger/$1', ['filter' => 'permission:yarn_beam.view']);
    $routes->post('production/yarn-beams/update-condition/(:num)', 'YarnBeamController::updateCondition/$1', ['filter' => 'permission:yarn_beam.create']);
    $routes->get('production/load-warps', 'YarnBeamController::loadedWarps', ['filter' => 'permission:yarn_beam.view']);

    // Production (Yarn Reports)
    $routes->get('production/yarn-reports', 'YarnReportController::index', ['filter' => 'permission:yarn_report.view']);
    $routes->get('production/yarns/create', 'ProductionYarnController::create', ['filter' => 'permission:yarn.create']);
    $routes->post('production/yarns/store', 'ProductionYarnController::store', ['filter' => 'permission:yarn.create']);
    $routes->get('production/yarns/edit/(:num)', 'ProductionYarnController::edit/$1', ['filter' => 'permission:yarn.edit']);
    $routes->post('production/yarns/update/(:num)', 'ProductionYarnController::update/$1', ['filter' => 'permission:yarn.edit']);
    $routes->get('production/yarns/delete/(:num)', 'ProductionYarnController::delete/$1', ['filter' => 'permission:yarn.delete']);

    // Bills
    $routes->get('bills', 'BillController::index', ['filter' => 'permission:bill.view']);
    $routes->get('bills/create', 'BillController::create', ['filter' => 'permission:bill.create']);
    $routes->post('bills/store', 'BillController::store', ['filter' => 'permission:bill.create']);
    $routes->get('bills/view/(:num)', 'BillController::view/$1', ['filter' => 'permission:bill.view']);
    $routes->get('bills/edit/(:num)', 'BillController::edit/$1', ['filter' => 'permission:bill.edit']);
    $routes->post('bills/update/(:num)', 'BillController::update/$1', ['filter' => 'permission:bill.edit']);
    $routes->get('bills/mark-open/(:num)', 'BillController::markAsOpen/$1', ['filter' => 'permission:bill.edit']);
    $routes->post('bills/void/(:num)', 'BillController::delete/$1', ['filter' => 'permission:bill.delete']);
    $routes->get('bills/payment/(:num)', 'BillController::recordPayment/$1', ['filter' => 'permission:bill.edit']);
    $routes->post('bills/payment/(:num)', 'BillController::storePayment/$1', ['filter' => 'permission:bill.edit']);
    $routes->get('bills/sync-zoho', 'BillController::syncFromZoho', ['filter' => 'permission:zoho.sync']);
    $routes->get('bills/print/(:num)', 'BillController::print/$1', ['filter' => 'permission:bill.view']);

    // Quotations
    $routes->get('quotations', 'QuotationController::index', ['filter' => 'permission:quotation.view']);
    $routes->get('quotations/create', 'QuotationController::create', ['filter' => 'permission:quotation.create']);
    $routes->post('quotations/store', 'QuotationController::store', ['filter' => 'permission:quotation.create']);
    $routes->get('quotations/view/(:num)', 'QuotationController::view/$1', ['filter' => 'permission:quotation.view']);
    $routes->get('quotations/edit/(:num)', 'QuotationController::edit/$1', ['filter' => 'permission:quotation.edit']);
    $routes->post('quotations/update/(:num)', 'QuotationController::update/$1', ['filter' => 'permission:quotation.edit']);
    $routes->get('quotations/delete/(:num)', 'QuotationController::delete/$1', ['filter' => 'permission:quotation.delete']);
    $routes->get('quotations/print/(:num)', 'QuotationController::print/$1', ['filter' => 'permission:quotation.view']);

    // Sales Orders
    $routes->get('sales_orders', 'SalesOrderController::index', ['filter' => 'permission:sales_order.view']);
    $routes->get('sales_orders/create', 'SalesOrderController::create', ['filter' => 'permission:sales_order.create']);
    $routes->post('sales_orders/store', 'SalesOrderController::store', ['filter' => 'permission:sales_order.create']);
    $routes->get('sales_orders/view/(:num)', 'SalesOrderController::view/$1', ['filter' => 'permission:sales_order.view']);
    $routes->get('sales_orders/edit/(:num)', 'SalesOrderController::edit/$1', ['filter' => 'permission:sales_order.edit']);
    $routes->post('sales_orders/update/(:num)', 'SalesOrderController::update/$1', ['filter' => 'permission:sales_order.edit']);
    $routes->get('sales_orders/delete/(:num)', 'SalesOrderController::delete/$1', ['filter' => 'permission:sales_order.delete']);
    $routes->get('sales_orders/print/(:num)', 'SalesOrderController::print/$1', ['filter' => 'permission:sales_order.view']);

    // Invoices
    $routes->get('invoices', 'InvoiceController::index', ['filter' => 'permission:invoice.view']);
    $routes->get('invoices/create', 'InvoiceController::create', ['filter' => 'permission:invoice.create']);
    $routes->post('invoices/store', 'InvoiceController::store', ['filter' => 'permission:invoice.create']);
    $routes->get('invoices/view/(:num)', 'InvoiceController::view/$1', ['filter' => 'permission:invoice.view']);
    $routes->get('invoices/edit/(:num)', 'InvoiceController::edit/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoices/update/(:num)', 'InvoiceController::update/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoices/delete/(:num)', 'InvoiceController::delete/$1', ['filter' => 'permission:invoice.delete']);
    $routes->get('invoices/payment/(:num)', 'InvoiceController::recordPayment/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoices/payment/(:num)', 'InvoiceController::storePayment/$1', ['filter' => 'permission:invoice.edit']);
    $routes->get('invoices/print/(:num)', 'InvoiceController::print/$1', ['filter' => 'permission:invoice.view']);
    $routes->get('invoices/mark-sent/(:num)', 'InvoiceController::markAsSent/$1');
    $routes->get('invoices/tracking', 'InvoiceController::tracking', ['filter' => 'permission:invoice.view']);
    $routes->post('invoices/update-waybill/(:num)', 'InvoiceController::updateWaybill/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoices/update-delivery-status/(:num)', 'InvoiceController::updateDeliveryStatus/$1', ['filter' => 'permission:invoice.edit']);
    $routes->get('invoices/doc-tracking', 'InvoiceController::docTracking', ['filter' => 'permission:invoice.view']);
    $routes->post('invoices/update-doc-details/(:num)', 'InvoiceController::updateDocDetails/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoices/update-doc-status/(:num)', 'InvoiceController::updateDocStatus/$1', ['filter' => 'permission:invoice.edit']);
    $routes->get('invoices/history/(:num)', 'InvoiceController::getHistory/$1');

    // Customer Receipts (Invoice Payments)
    $routes->get('invoice_payments', 'InvoicePaymentController::index', ['filter' => 'permission:invoice.view']);
    $routes->get('invoice_payments/view/(:num)', 'InvoicePaymentController::view/$1', ['filter' => 'permission:invoice.view']);
    $routes->get('invoice_payments/edit/(:num)', 'InvoicePaymentController::edit/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoice_payments/update/(:num)', 'InvoicePaymentController::update/$1', ['filter' => 'permission:invoice.edit']);
    $routes->post('invoice_payments/delete/(:num)', 'InvoicePaymentController::delete/$1', ['filter' => 'permission:invoice.edit']);

    // Vendor Payments
    $routes->get('payments', 'PaymentController::index', ['filter' => 'permission:bill.view']);
    $routes->get('payments/view/(:num)', 'PaymentController::view/$1', ['filter' => 'permission:bill.view']);
    $routes->get('payments/edit/(:num)', 'PaymentController::edit/$1', ['filter' => 'permission:bill.edit']);
    $routes->post('payments/update/(:num)', 'PaymentController::update/$1', ['filter' => 'permission:bill.edit']);
    $routes->post('payments/delete/(:num)', 'PaymentController::delete/$1', ['filter' => 'permission:bill.edit']);

    // Agent Payments
    $routes->get('agent-payments', 'AgentPaymentController::index', ['filter' => 'permission:agent_payments.view']);
    $routes->get('agent-payments/datatable', 'AgentPaymentController::datatable', ['filter' => 'permission:agent_payments.view']);
    $routes->get('agent-payments/create', 'AgentPaymentController::create', ['filter' => 'permission:agent_payments.create']);
    $routes->get('agent-payments/create/(:num)', 'AgentPaymentController::create/$1', ['filter' => 'permission:agent_payments.create']);
    $routes->post('agent-payments/store', 'AgentPaymentController::store', ['filter' => 'permission:agent_payments.create']);
    $routes->get('agent-payments/view/(:num)', 'AgentPaymentController::view/$1', ['filter' => 'permission:agent_payments.view']);
    $routes->get('agent-payments/delete/(:num)', 'AgentPaymentController::delete/$1', ['filter' => 'permission:agent_payments.delete']);
    $routes->get('agent-payments/pending-commissions/(:num)', 'AgentPaymentController::getPendingCommissions/$1', ['filter' => 'permission:agent_payments.view']);
    $routes->get('agent-payments/reports', 'AgentPaymentController::reports', ['filter' => 'permission:agent_payments.reports']);
    $routes->get('agent-payments/preview-report', 'AgentPaymentController::previewReport', ['filter' => 'permission:agent_payments.view']);

    // Master Data AJAX
    $routes->get('master-data/states/(:num)', 'MasterDataController::getStatesByCountry/$1');
    $routes->get('master-data/product-by-barcode', 'MasterDataController::getProductByBarcode');

    // API Routes for n8n Integration
    $routes->group('api', ['filter' => 'apiauth'], function ($routes) {
        // Customer APIs
        $routes->get('customers', 'ApiController::getCustomers');
        $routes->get('customers/(:num)', 'ApiController::getCustomer/$1');
        $routes->put('customers/(:num)', 'ApiController::updateCustomer/$1');
        $routes->post('customers/(:num)/zoho-link', 'ApiController::linkCustomerToZoho/$1');
        $routes->post('customers/(:num)/address/sync', 'ApiController::syncCustomerAddress/$1');

        // Vendor APIs
        $routes->get('vendors', 'ApiController::getVendors');
        $routes->get('vendors/(:num)', 'ApiController::getVendor/$1');
        $routes->put('vendors/(:num)', 'ApiController::updateVendor/$1');
        $routes->post('vendors/(:num)/zoho-link', 'ApiController::linkVendorToZoho/$1');
        $routes->post('vendors/(:num)/address/sync', 'ApiController::syncVendorAddress/$1');
    });

    // Tax Routes
    $routes->group('settings/taxes', function ($routes) {
        $routes->get('', 'TaxController::index', ['filter' => 'permission:tax.view']);
        $routes->get('new', 'TaxController::new', ['filter' => 'permission:tax.create']);
        $routes->post('create', 'TaxController::create', ['filter' => 'permission:tax.create']);
        $routes->get('edit/(:num)', 'TaxController::edit/$1', ['filter' => 'permission:tax.edit']);
        $routes->post('update/(:num)', 'TaxController::update/$1', ['filter' => 'permission:tax.edit']);
        $routes->get('delete/(:num)', 'TaxController::delete/$1', ['filter' => 'permission:tax.delete']);
    });
        $routes->group('bulk-upload', ['filter' => 'auth', 'permission' => 'customer.bulk_upload|vendor.bulk_upload'], function($routes){
        $routes->get('form', 'BulkUploadController::showForm');
        $routes->post('process', 'BulkUploadController::process');
    });
});

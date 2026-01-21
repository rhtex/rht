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
    $routes->get('agents/edit/(:num)', 'AgentController::edit/$1', ['filter' => 'permission:agent.edit']);
    $routes->post('agents/update/(:num)', 'AgentController::update/$1', ['filter' => 'permission:agent.edit']);
    $routes->get('agents/delete/(:num)', 'AgentController::delete/$1', ['filter' => 'permission:agent.delete']);

    // Transports
    $routes->get('transports', 'TransportController::index', ['filter' => 'permission:transport.view']);
    $routes->get('transports/create', 'TransportController::create', ['filter' => 'permission:transport.create']);
    $routes->post('transports/store', 'TransportController::store', ['filter' => 'permission:transport.create']);
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
    $routes->get('customers/create', 'CustomerController::create', ['filter' => 'permission:customer.create']);
    $routes->post('customers/store', 'CustomerController::store', ['filter' => 'permission:customer.create']);
    $routes->get('customers/edit/(:num)', 'CustomerController::edit/$1', ['filter' => 'permission:customer.edit']);
    $routes->post('customers/update/(:num)', 'CustomerController::update/$1', ['filter' => 'permission:customer.edit']);
    $routes->get('customers/delete/(:num)', 'CustomerController::delete/$1', ['filter' => 'permission:customer.delete']);

    // Purchase (Vendors) Management
    $routes->get('vendors', 'VendorController::index', ['filter' => 'permission:vendor.view']);
    $routes->get('vendors/create', 'VendorController::create', ['filter' => 'permission:vendor.create']);
    $routes->post('vendors/store', 'VendorController::store', ['filter' => 'permission:vendor.create']);
    $routes->get('vendors/edit/(:num)', 'VendorController::edit/$1', ['filter' => 'permission:vendor.edit']);
    $routes->post('vendors/update/(:num)', 'VendorController::update/$1', ['filter' => 'permission:vendor.edit']);
    $routes->get('vendors/delete/(:num)', 'VendorController::delete/$1', ['filter' => 'permission:vendor.delete']);

    // Master Data AJAX
    $routes->get('master-data/states/(:num)', 'MasterDataController::getStatesByCountry/$1');

    // Zoho Integration
    $routes->get('zoho-settings', 'ZohoSettingsController::index', ['filter' => 'permission:zoho.view']);
    $routes->post('zoho-settings/update', 'ZohoSettingsController::update', ['filter' => 'permission:zoho.edit']);
    $routes->get('customers/sync-zoho', 'CustomerController::syncZoho', ['filter' => 'permission:zoho.sync']);
    $routes->get('vendors/sync-zoho', 'VendorController::syncZoho', ['filter' => 'permission:zoho.sync']);
});

<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */
$routes->get('/access-denied', 'PermissionsController::accessDenied');

$routes->match(['get', 'post'], 'SigninController/loginAuth', 'SigninController::loginAuth');
$routes->get('/signin', 'SigninController::index');
$routes->get('/dashboard', 'DashboardController::index');
$routes->get('/', 'SigninController::index');
$routes->get('/combinations', 'CombinationsController::index');
$routes->get('/combinations/approve/(:num)', 'CombinationsController::approve/$1');
$routes->get('/combinations/reject/(:num)', 'CombinationsController::reject/$1');
$routes->get('/insertWeftColorsFromCombinations', 'YarnColorsController::insertWeftColorsFromCombinations');

$routes->get('/profile', 'ProfileController::index', ['filter' => 'authGuard']);
$routes->get('/add_employee', 'EmployeeController::add_employee');
$routes->get('/list_employees', 'EmployeeController::list_employees');
$routes->get('/calculate_salary', 'EmployeeController::calculate_salary');
$routes->get('/view_salary', 'EmployeeController::view_salary');
$routes->match(['get', 'post'], '/create_employee', 'EmployeeController::create_employee');
$routes->get('/edit_employee/(:num)', 'EmployeeController::edit_employee/$1');
$routes->get('/view_employee/(:num)', 'EmployeeController::view_employee/$1');
$routes->match(['get', 'post'], '/update_employee', 'EmployeeController::update_employee');
$routes->get('/view_attendance', 'AttendanceController::view_attendance');
$routes->get('/add_attendance', 'AttendanceController::add_attendance');
$routes->post('/store_attendance', 'AttendanceController::store_attendance');
$routes->get('/search_by_month_year_ajax', 'AttendanceController::search_by_month_year_ajax');
$routes->get('/search_by_date_employee_ajax', 'AttendanceController::search_by_date_employee_ajax');
$routes->get('/get_attendance_data', 'AttendanceController::get_attendance_data');
$routes->get('/salary-history', 'SalaryHistoryController::index');            // Show all salary histories
$routes->get('/salary-history/create', 'SalaryHistoryController::create');     // Show the form to create a new salary history record
$routes->post('/salary-history/store', 'SalaryHistoryController::store');       // Store a new salary history record
$routes->get('/salary-history/edit/(:segment)', 'SalaryHistoryController::edit/$1');   // Edit an existing salary history record
$routes->post('/salary-history/update/(:segment)', 'SalaryHistoryController::update/$1'); // Update a salary history record
$routes->get('/salary-history/delete/(:segment)', 'SalaryHistoryController::delete/$1'); // Delete a salary history record
$routes->get('/salary-history/show/(:segment)', 'SalaryHistoryController::show/$1');
$routes->get('/salary-history/viewemployeesalaryhistory/', 'SalaryHistoryController::viewemployeesalaryhistory');
$routes->post('/salary-history/getSalaryHistory', 'SalaryHistoryController::getSalaryHistory');
$routes->post('/salary-history/generate', 'EmployeeController::generate');


//Transport
$routes->get('/list_transport', 'TransportController::index');
$routes->get('/create_transport', 'TransportController::create');
$routes->post('/store_transport', 'TransportController::store');
$routes->get('/edit_transport/(:num)', 'TransportController::edit/$1');
$routes->post('/update_transport/(:num)', 'TransportController::update/$1');
$routes->get('/delete_transport/(:num)', 'TransportController::delete/$1');
$routes->get('/view_transport/(:num)', 'TransportController::view/$1');

//Transport Logs
$routes->get('/list_courier_in', 'TransportLogsController::index');
$routes->get('/list_courier_out', 'TransportLogsController::list_courier_out');
$routes->post('/create_transport_logs', 'TransportLogsController::create');
$routes->post('/create_transport_logs_out', 'TransportLogsController::create_out');
$routes->get('/add_new_courier_in', 'TransportLogsController::add_new_courier_in');
$routes->get('/add_new_courier_out', 'TransportLogsController::add_new_courier_out');
$routes->get('/courier_in_edit/(:num)', 'TransportLogsController::edit_courier_in/$1');
$routes->get('/courier_out_edit/(:num)', 'TransportLogsController::edit_courier_out/$1');
$routes->post('/update_transport_logs/(:num)', 'TransportLogsController::update/$1');
$routes->post('/update_transport_logs_out/(:num)', 'TransportLogsController::update_out/$1');

//Permissions
$routes->get('/permissions', 'PermissionsController::index');
$routes->post('/permissions/fetchPermissions/', 'PermissionsController::fetchPermissions');
$routes->post('/permissions/updatePermissions', 'PermissionsController::updatePermissions');


//Roles
$routes->get('/roles', 'RolesController::index');
$routes->get('/roles/create', 'RolesController::create');
$routes->post('/roles/store', 'RolesController::store');
$routes->get('/roles/edit/(:num)', 'RolesController::edit/$1');
$routes->post('/roles/update/(:num)', 'RolesController::update/$1');
$routes->get('/roles/delete/(:num)', 'RolesController::delete/$1');

//Modules
$routes->get('/modules', 'ModulesController::index');
$routes->get('/modules/create', 'ModulesController::create');
$routes->post('/modules/store', 'ModulesController::store');
$routes->get('/modules/edit/(:num)', 'ModulesController::edit/$1');
$routes->post('/modules/update/(:num)', 'ModulesController::update/$1');
$routes->get('/modules/delete/(:num)', 'ModulesController::delete/$1');

//Banks
$routes->get('/banks', 'BankController::index');
$routes->get('/banks/create', 'BankController::create');
$routes->post('/banks/store', 'BankController::store');
$routes->get('/banks/edit/(:num)', 'BankController::edit/$1');
$routes->post('/banks/update/(:num)', 'BankController::update/$1');
$routes->get('/banks/show/(:num)', 'BankController::show/$1');
$routes->get('/banks/add-transaction', 'BankController::addTransaction');
$routes->post('/banks/store-transaction', 'BankController::storeTransaction');
$routes->get('/banks/list-statements', 'BankController::listStatements');
$routes->get('/banks/view-statement/(:num)', 'BankController::viewStatement/$1');
$routes->get('/banks/edit-statement/(:num)', 'BankController::editStatement/$1');
$routes->post('/banks/update-statement/(:num)', 'BankController::updateStatement/$1');


//employees
$routes->get('/employee/createUserFromEmployee/(:num)', 'EmployeeController::createUserFromEmployee/$1');
$routes->get('users', 'UsersController::index');
$routes->get('users/view/(:num)', 'UsersController::view/$1');
$routes->post('users/assignRole/(:num)', 'UsersController::assignRole/$1');
$routes->get('logout', 'SigninController::logout');


//customers
// $routes->get('/customers', 'CustomersController::index');
// $routes->get('/customers/create', 'CustomersController::create');
// $routes->get('/customers/view/(:num)', 'CustomersController::view/$1');
// $routes->get('/customers/edit/(:num)', 'CustomersController::edit/$1');
// $routes->post('/customers/update/(:num)', 'CustomersController::update/$1');
// $routes->get('/customers/delete/(:num)', 'CustomersController::delete/$1');


//agents
$routes->get('agents', 'AgentsController::index');
$routes->get('agents/view/(:num)', 'AgentsController::view/$1');
$routes->get('agents/create', 'AgentsController::create');
$routes->post('agents/store', 'AgentsController::store');
$routes->get('agents/edit/(:num)', 'AgentsController::edit/$1');
$routes->post('agents/update/(:num)', 'AgentsController::update/$1');
$routes->get('agents/delete/(:num)', 'AgentsController::delete/$1');


// app/Config/Routes.php
$routes->get('/oauth/authorize', 'OAuthController::authorize');
$routes->get('/oauth/callback', 'OAuthController::callback');
$routes->get('/oauth/refresh', 'OAuthController::refreshToken');
$routes->get('/oauth/list-tokens', 'OAuthController::listTokens'); // For debugging
$routes->get('/invoices', 'ZohoBooksController::getInvoices');
$routes->get('/fetchallcontactsfromzoho', 'ZohoBooksController::get_all_contacts');
$routes->get('/test1', 'ZohoBooksController::test1');
$routes->get('/customers', 'CustomerController::index');
$routes->get('/customers/create', 'CustomerController::create'); // Display create form
$routes->post('/customers/create', 'CustomerController::create');
$routes->get('/customers/edit/(:num)', 'CustomerController::edit/$1'); // To show the edit customer form
$routes->post('/customers/edit/(:num)', 'CustomerController::edit/$1'); // To handle the form submission for updating an existing customer
$routes->get('/customers/delete/(:num)', 'CustomerController::delete/$1'); // To delete a customer
$routes->get('/customers/view/(:num)', 'CustomerController::view/$1');
$routes->post('/getStates', 'StateController::fetchStates');
$routes->get('/suppliers', 'SupplierController::index');
$routes->post('/suppliers/create', 'SupplierController::create');
$routes->get('/suppliers/create', 'SupplierController::create');
$routes->get('/suppliers/getSuppliers', 'SupplierController::getSuppliers');
$routes->get('/suppliers/edit/(:num)', 'SupplierController::edit/$1');
$routes->post('/suppliers/update/(:num)', 'SupplierController::update/$1');
<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['url', 'form', 'time', 'app'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');

        // Check for calendar reminders due today
        $this->checkCalendarReminders();
    }

    /**
     * Check for due reminders and create notifications
     */
    protected function checkCalendarReminders()
    {
        // Only run check if session exists and user is logged in
        if (session()->has('isLoggedIn')) {
            $reminderModel = new \App\Models\CalendarReminderModel();
            $notificationModel = new \App\Models\NotificationModel();
            
            $dueReminders = $reminderModel->getDueReminders();
            
            foreach ($dueReminders as $reminder) {
                $notificationModel->insert([
                    'type' => 'reminder',
                    'title' => 'Reminder: ' . $reminder['title'],
                    'message' => $reminder['description'] ?? 'No description provided.',
                    'link' => 'calendar',
                    'reference_id' => $reminder['id'],
                    'is_read' => 0
                ]);

                $reminderModel->update($reminder['id'], ['is_notified' => 1]);
            }
        }
    }
}

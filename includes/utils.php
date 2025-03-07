<?php
/**
 * Utility functions for the Expense Tracker application
 */
class Utils {
    /**
     * Clean input data to prevent XSS attacks
     * @param string $data
     * @return string
     */
    public static function cleanInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    /**
     * Validate email format
     * @param string $email
     * @return bool
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Format amount to currency
     * @param float $amount
     * @return string
     */
    public static function formatCurrency($amount) {
        return '₹' . number_format($amount, 2);
    }

    /**
     * Format date to readable format
     * @param string $date
     * @return string
     */
    public static function formatDate($date) {
        return date('F j, Y', strtotime($date));
    }

    /**
     * Get current month name
     * @return string
     */
    public static function getCurrentMonth() {
        return date('F');
    }

    /**
     * Get current year
     * @return string
     */
    public static function getCurrentYear() {
        return date('Y');
    }

    /**
     * Redirect to a specific page
     * @param string $url
     */
    public static function redirect($url) {
        header("Location: $url");
        exit();
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get months array for reports
     * @return array
     */
    public static function getMonths() {
        return [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December'
        ];
    }
}
?> 
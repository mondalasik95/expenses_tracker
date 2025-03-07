# Expense Tracker

A comprehensive web application for tracking personal expenses, built with PHP and MySQL.

## Features

- **User Authentication**
  - Secure login and registration
  - Password hashing for security
  - Profile management

- **Expense Management**
  - Add, edit, and delete expenses
  - Categorize expenses
  - Filter expenses by category and date range

- **Reporting and Analytics**
  - Monthly expense reports
  - Category-wise expense breakdown
  - Visual charts and graphs using Chart.js

- **User Interface**
  - Responsive design with Bootstrap 5
  - Interactive UI with jQuery
  - Beautiful icons with Font Awesome
  - User-friendly notifications with SweetAlert2

## Technologies Used

- **Backend**
  - PHP (Object-Oriented)
  - MySQL Database

- **Frontend**
  - HTML5, CSS3, JavaScript
  - Bootstrap 5
  - jQuery
  - Chart.js for data visualization
  - Font Awesome for icons
  - SweetAlert2 for notifications

## Installation

1. **Clone the repository**
   ```
   git clone https://github.com/mondalasik95/expenses_tracker.git
   ```

2. **Set up the database**
   - Create a MySQL database
   - Import the `database/expense_tracker.sql` file

3. **Configure database connection**
   - Open `config/database.php`
   - Update the database credentials if needed:
     ```php
     private $host = "localhost";
     private $db_name = "expense_tracker";
     private $username = "root";
     private $password = "";
     ```

4. **Deploy to a PHP server**
   - Copy the files to your web server directory
   - Make sure the server has PHP and MySQL support

5. **Access the application**
   - Open your browser and navigate to the application URL
   - Register a new account and start tracking your expenses

## Project Structure

```
expense-tracker/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
├── config/
│   └── database.php
├── database/
│   └── expense_tracker.sql
├── includes/
│   ├── footer.php
│   ├── header.php
│   └── utils.php
├── models/
│   ├── Category.php
│   ├── Expense.php
│   └── User.php
├── views/
│   ├── expense_form.php
│   └── expense_list.php
├── index.php
├── login.php
├── register.php
├── logout.php
├── expenses.php
└── README.md
```

## Usage

1. **Register/Login**
   - Create a new account or login with existing credentials

2. **Dashboard**
   - View summary of expenses
   - See monthly and category-wise expense charts

3. **Manage Expenses**
   - Add new expenses with category, amount, date, and description
   - Edit or delete existing expenses
   - Filter expenses by category or date range

4. **Reports**
   - View detailed expense reports
   - Analyze spending patterns

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgements

- [Bootstrap](https://getbootstrap.com/)
- [Chart.js](https://www.chartjs.org/)
- [Font Awesome](https://fontawesome.com/)
- [SweetAlert2](https://sweetalert2.github.io/)
- [jQuery](https://jquery.com/) 
  
# expenses_tracker

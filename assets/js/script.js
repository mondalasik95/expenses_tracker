/**
 * Expense Tracker - Main JavaScript
 */

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Delete confirmation for expenses
    const deleteExpenseButtons = document.querySelectorAll('.delete-expense');
    if (deleteExpenseButtons) {
        deleteExpenseButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'expenses.php?action=delete&id=' + id;
                    }
                });
            });
        });
    }

    // Delete confirmation for categories
    const deleteCategoryButtons = document.querySelectorAll('.delete-category');
    if (deleteCategoryButtons) {
        deleteCategoryButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Deleting a category will affect related expenses!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'categories.php?action=delete&id=' + id;
                    }
                });
            });
        });
    }

    // Date picker initialization
    const datePickers = document.querySelectorAll('.datepicker');
    if (datePickers) {
        datePickers.forEach(picker => {
            picker.addEventListener('focus', function() {
                this.type = 'date';
            });
            picker.addEventListener('blur', function() {
                if (!this.value) {
                    this.type = 'text';
                }
            });
        });
    }

    // Filter expenses by category
    const categoryFilter = document.getElementById('category-filter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            const categoryId = this.value;
            if (categoryId) {
                window.location.href = 'expenses.php?category_id=' + categoryId;
            } else {
                window.location.href = 'expenses.php';
            }
        });
    }

    // Filter expenses by date range
    const dateRangeForm = document.getElementById('date-range-form');
    if (dateRangeForm) {
        dateRangeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate) {
                window.location.href = 'expenses.php?start_date=' + startDate + '&end_date=' + endDate;
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select both start and end dates',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    // Show success messages with SweetAlert
    const urlParams = new URLSearchParams(window.location.search);
    const successParam = urlParams.get('success');
    const errorParam = urlParams.get('error');
    
    if (successParam) {
        let message = '';
        switch (successParam) {
            case 'expense_added':
                message = 'Expense has been added successfully!';
                break;
            case 'expense_updated':
                message = 'Expense has been updated successfully!';
                break;
            case 'expense_deleted':
                message = 'Expense has been deleted successfully!';
                break;
            case 'category_added':
                message = 'Category has been added successfully!';
                break;
            case 'category_updated':
                message = 'Category has been updated successfully!';
                break;
            case 'category_deleted':
                message = 'Category has been deleted successfully!';
                break;
            case 'profile_updated':
                message = 'Your profile has been updated successfully!';
                break;
            case 'password_changed':
                message = 'Your password has been changed successfully!';
                break;
            default:
                message = 'Operation completed successfully!';
        }
        
        Swal.fire({
            title: 'Success!',
            text: message,
            icon: 'success',
            confirmButtonText: 'OK'
        });
    }
    
    if (errorParam) {
        let message = '';
        switch (errorParam) {
            case 'category_has_expenses':
                message = 'Cannot delete category because it has associated expenses!';
                break;
            case 'invalid_id':
                message = 'Invalid ID provided!';
                break;
            case 'unauthorized':
                message = 'You are not authorized to perform this action!';
                break;
            case 'current_password':
                message = 'Current password is incorrect!';
                break;
            default:
                message = 'An error occurred. Please try again!';
        }
        
        Swal.fire({
            title: 'Error!',
            text: message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }

    // Initialize charts if they exist on the page
    initializeCharts();
});

/**
 * Initialize charts on the dashboard and reports page
 */
function initializeCharts() {
    // Monthly expenses chart
    const monthlyChartCanvas = document.getElementById('monthlyExpensesChart');
    if (monthlyChartCanvas) {
        // Get chart data from the data attribute
        const chartData = JSON.parse(monthlyChartCanvas.getAttribute('data-chart'));
        
        new Chart(monthlyChartCanvas, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Monthly Expenses',
                    data: chartData.data,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value;
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₹' + context.raw;
                            }
                        }
                    }
                }
            }
        });
    }

    // Category expenses chart
    const categoryChartCanvas = document.getElementById('categoryExpensesChart');
    if (categoryChartCanvas) {
        // Get chart data from the data attribute
        const chartData = JSON.parse(categoryChartCanvas.getAttribute('data-chart'));
        
        new Chart(categoryChartCanvas, {
            type: 'doughnut',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.data,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#C9CBCF', '#7ED321', '#50E3C2', '#FF5A5E'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return label + ': ₹' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
} 
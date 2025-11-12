    </div> <!-- End content-area -->
</div> <!-- End main-content -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Common JavaScript -->
<script>
    // Toggle sidebar on mobile
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('active');
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const isClickInsideSidebar = sidebar && sidebar.contains(event.target);
        const isClickOnToggle = menuToggle && menuToggle.contains(event.target);

        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('active')) {
            if (!isClickInsideSidebar && !isClickOnToggle) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Logout function
    async function logout() {
        if (confirm('Are you sure you want to logout?')) {
            try {
                const response = await fetch('<?php echo APP_URL; ?>/api/auth/logout.php', {
                    method: 'POST'
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = '<?php echo APP_URL; ?>/pages/login.php';
                } else {
                    alert('Logout failed. Please try again.');
                }
            } catch (error) {
                console.error('Logout error:', error);
                alert('An error occurred. Please try again.');
            }
        }
    }

    // Show alert message
    function showAlert(message, type = 'info', container = '#alert-container') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        $(container).html(alertHtml);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $(container + ' .alert').alert('close');
        }, 5000);
    }

    // Format currency
    function formatCurrency(amount) {
        return '<?php echo CURRENCY; ?> ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    // Format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // Initialize DataTables with default settings
    function initDataTable(selector, options = {}) {
        const defaultOptions = {
            pageLength: <?php echo RECORDS_PER_PAGE; ?>,
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search..."
            }
        };

        return $(selector).DataTable({ ...defaultOptions, ...options });
    }

    // Check session periodically (every 5 minutes)
    setInterval(async () => {
        try {
            const response = await fetch('<?php echo APP_URL; ?>/api/auth/check_session.php');
            const result = await response.json();

            if (!result.success) {
                alert('Your session has expired. Please login again.');
                window.location.href = '<?php echo APP_URL; ?>/pages/login.php';
            }
        } catch (error) {
            console.error('Session check error:', error);
        }
    }, 300000); // 5 minutes
</script>

<?php if (isset($extraScripts)) echo $extraScripts; ?>

</body>
</html>

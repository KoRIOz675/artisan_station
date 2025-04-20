<div class="container admin-dashboard-container" style="padding: 20px;">

    <h1>Admin Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_username'] ?? 'Admin'); ?>!</p>

    <hr style="margin: 20px 0; background-color: #800000;">

    <!-- Stats/Summary Boxes (Example) -->
    <div class="admin-stats" style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
        <div class="stat-box" style="flex: 1; min-width: 150px; background-color: #e9ecef; padding: 15px; border-radius: 5px; text-align: center;">
            <h4>Total Users</h4>
            <p style="font-size: 1.5em; font-weight: bold;"><?php echo htmlspecialchars($data['userCount'] ?? '0'); ?></p>
            <a href="<?php echo URLROOT; ?>/admin/manageUsers" class="btn btn-primary" style="width: 10em;">Manage users</a>
        </div>
        <div class="stat-box" style="flex: 1; min-width: 150px; background-color: #e9ecef; padding: 15px; border-radius: 5px; text-align: center;">
            <h4>Total Products</h4>
            <p style="font-size: 1.5em; font-weight: bold;"><?php echo htmlspecialchars($data['productCount'] ?? '0'); ?></p>
            <a href="<?php echo URLROOT; ?>/admin/manageProducts" class="btn btn-primary" style="width: 10em;">Manage products</a>
        </div>
        <div class="stat-box" style="flex: 1; min-width: 150px; background-color: #e9ecef; padding: 15px; border-radius: 5px; text-align: center;">
            <h4>Pending Orders</h4>
            <p style="font-size: 1.5em; font-weight: bold;"><?php echo htmlspecialchars($data['pendingOrderCount'] ?? '0'); ?></p>
            <a href="<?php echo URLROOT; ?>/admin/manageOrders" class="btn btn-primary" style="width: 10em;">Manage order</a>
        </div>
    </div>

    <hr style="margin: 20px 0; background-color: #800000;">
    <div style="display: flex; flex-wrap: wrap; gap: 30px;">
        <div style="flex: 1; min-width: 250px;">
            <h2>Admin Actions</h2>
            <ul class="admin-actions-list" style="list-style: none; padding: 0; margin: 1em 0;">
                <li style="margin-bottom: 10px;"><a href="<?php echo URLROOT; ?>/admin/manageEvents" class="btn btn-primary" style="margin-right: 0.6em; width: 15em;">Manage Events</a></li>
                <li style="margin-bottom: 10px;"><a href="<?php echo URLROOT; ?>/admin/manageCategories" class="btn btn-primary" style="margin-right: 0.6em; width: 15em;">Manage Categories</a></li>
                <li style="margin-bottom: 10px;"><a href="<?php echo URLROOT; ?>/admin/manageFeatured" class="btn btn-primary" style="margin-right: 0.6em; width: 15em;">Manage Featured Content</a></li>
                <li style="margin-bottom: 10px;"><a href="<?php echo URLROOT; ?>/admin/siteSettings" class="btn btn-primary" style="margin-right: 0.6em; width: 15em;">Site Settings</a></li>
            </ul>
        </div>
        <div style="flex: 2; min-width: 300px;">
            <h2>Website charts</h2>
            <div style="margin-bottom: 40px;">
                <h3>New User Registrations (Last 7 Days)</h3>
                <canvas id="registrationsChart" height="100"></canvas>
            </div>

            <div>
                <h3>Items Sold (Last 10 Days)</h3>
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- Helper function to prepare chart data (fills date gaps) ---
            function prepareChartData(rawData, dateKey, countKey, numDays) {
                const labels = [];
                const dataCounts = [];
                const dataMap = new Map(rawData.map(item => [item[dateKey], parseInt(item[countKey] || 0)]));
                const today = new Date();

                for (let i = numDays - 1; i >= 0; i--) {
                    const date = new Date(today);
                    date.setDate(today.getDate() - i);
                    const formattedDate = date.toISOString().split('T')[0]; // YYYY-MM-DD format
                    labels.push(formattedDate);
                    dataCounts.push(dataMap.get(formattedDate) || 0); // Use data or 0 if date missing
                }

                return {
                    labels,
                    dataCounts
                };
            }

            // --- Registration Chart ---
            const regRawData = <?php echo json_encode($data['registrationChartData'] ?? []); ?>;
            const regChartData = prepareChartData(regRawData, 'registration_date', 'count', 7); // 7 days

            const regCtx = document.getElementById('registrationsChart')?.getContext('2d');
            if (regCtx) {
                new Chart(regCtx, {
                    type: 'line', // Or 'bar'
                    data: {
                        labels: regChartData.labels,
                        datasets: [{
                            label: 'New Users',
                            data: regChartData.dataCounts,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1,
                            fill: true // Fill area below line
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { // Ensure only integers on Y-axis
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            } // Hide legend if only one dataset
                        }
                    }
                });
            } else {
                console.error("Canvas element for registrations chart not found");
            }


            // --- Sales Chart ---
            const salesRawData = <?php echo json_encode($data['salesChartData'] ?? []); ?>;
            const salesChartData = prepareChartData(salesRawData, 'sale_date', 'total_quantity', 10); // 10 days

            const salesCtx = document.getElementById('salesChart')?.getContext('2d');
            if (salesCtx) {
                new Chart(salesCtx, {
                    type: 'bar', // Or 'line'
                    data: {
                        labels: salesChartData.labels,
                        datasets: [{
                            label: 'Items Sold',
                            data: salesChartData.dataCounts,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)', // Blue bars
                            borderColor: 'rgb(54, 162, 235)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                } // Integer ticks
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            } else {
                console.error("Canvas element for sales chart not found");
            }

        });
    </script>
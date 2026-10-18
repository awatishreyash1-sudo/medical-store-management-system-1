 <!-- sidebar.php -->

<style>
.sidebar a {
    display: flex;
    align-items: center;
    padding: 10px 10px;
    margin-bottom: 12px;
    background: #e9f1ff; /* Light bluish card */
    border: none;
    border-radius: 14px;
    color: #0a3d62;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 1px 4px rgba(10, 61, 98, 0.1);
}

.sidebar a:hover {
    background: linear-gradient(145deg, #0056b3, #3399ff);
    color: #ffffff;
    transform: translateX(6px) scale(1.02);
    box-shadow: 0 4px 16px rgba(0, 123, 255, 0.35);
}

.sidebar a i {
    margin-right: 12px;
    font-size: 1.25rem;
    transition: 0.3s;
}

.sidebar a:hover i {
    transform: scale(1.1);
}

.sidebar a.active {
    background: #007bff;
    color: white;
    font-weight: 600;
    box-shadow: 0 4px 14px rgba(0, 123, 255, 0.4);
}



/* Mobile view */
@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 230px;
        height: 100vh;
        z-index: 1050;
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
    }

    .sidebar.show {
        transform: translateX(0);
    }

    .sidebar-toggle {
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1100;
        background-color: #007bff;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 123, 255, 0.3);
    }
}

</style>

<!-- Toggle button (visible on small screens) -->
<button class="sidebar-toggle d-md-none" onclick="document.querySelector('.sidebar').classList.toggle('show')">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar -->
<div class="sidebar d-none d-md-block col-md-2">
            <h4><i class="bi bi-shop-window"></i> Medical Shop</h4>
            <a href="#"><i class="bi bi-house-door-fill"></i> Dashboard</a>

<a href="salesman_id.php">
    <i class="bi bi-person-plus-fill"></i> Add Salesman
</a>

<a href="view_salesman.php">
    <i class="bi bi-people-fill"></i> Salesman List
</a>



<a href="../index.php">
    <i class="bi bi-arrow-left-circle-fill"></i> Back
</a>

<a href="../logout.php">
    <i class="bi bi-box-arrow-right"></i> Logout
</a>

        </div>
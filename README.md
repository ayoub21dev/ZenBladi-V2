# ZenBladi-V2

ZenBladi-V2 is a web application, likely an e-commerce or marketplace platform, built primarily with **PHP** and **MySQL**. It features distinct user roles for Clients, Sellers, and Administrators, providing a structured environment for managing products, orders, and user accounts.

The repository has been reorganized into a more standard and maintainable structure to improve clarity and development workflow.

## 🚀 Project Structure

The project now follows a clear separation of concerns, dividing the codebase into `src` (source code) and `public` (publicly accessible assets).

| Directory | Purpose |
| :--- | :--- |
| `src/backend` | Contains core server-side logic, including database connection (`db.php`) and order processing (`create_order.php`). |
| `src/backend/db` | Holds the database schema file (`database.sql`). |
| `src/frontend` | Contains the main PHP files for the user-facing application, including the home page, login, and sign-up pages. |
| `src/frontend/admin` | PHP files specific to the Administrator dashboard and management panels. |
| `src/frontend/client` | PHP files for the Client dashboard, including account management and order history. |
| `src/frontend/seller` | PHP files for the Seller dashboard, including product management and order viewing. |
| `src/includes` | Reusable PHP components like the header and session configuration. |
| `public/assets` | All static assets, including CSS, JavaScript, and images, which are directly accessible by the browser. |
| `public/assets/css` | All CSS files, organized into subdirectories for Admin, Client, and Seller specific styles. |
| `public/assets/js` | JavaScript files for client-side interactivity. |
| `public/assets/images` | General images used across the site (e.g., logos, headers). |
| `public/assets/img_products` | Directory for product images. |

## 🛠️ Installation and Setup

To get a local copy up and running, follow these steps.

### Prerequisites

*   A web server with PHP support (e.g., Apache, Nginx)
*   MySQL or MariaDB database server
*   PHP extensions (e.g., `mysqli`, `session`)

### Steps

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/ayoub21dev/ZenBladi-V2.git
    cd ZenBladi-V2
    ```

2.  **Database Setup:**
    *   Create a new MySQL database (e.g., `zenbladi_v2`).
    *   Import the database schema from the provided file:
        ```bash
        mysql -u your_user -p zenbladi_v2 < src/backend/db/database.sql
        ```

3.  **Configure Database Connection:**
    *   Open `src/backend/db.php` and update the database connection details (`$servername`, `$username`, `$password`, `$dbname`) to match your local setup.

4.  **Web Server Configuration:**
    *   Point your web server's document root to the `ZenBladi-V2/` directory.
    *   Ensure PHP is configured correctly and the necessary extensions are enabled.

5.  **Access the Application:**
    *   Open your web browser and navigate to the configured URL (e.g., `http://localhost/ZenBladi-V2/src/frontend/index.php`).

## 🔑 Key Features

The application supports a multi-role structure:

*   **Client:** Sign up, log in, browse products, place orders, view order history, and manage account details.
*   **Seller:** Sign up, log in, add new products, manage existing products, view their orders, and manage their profile.
*   **Admin:** Log in, manage users (Clients and Sellers), manage products, and oversee orders through dedicated panels.

## 🤝 Contributing

This project is a great foundation for a web application. If you wish to contribute, please feel free to fork the repository and submit a pull request.

## 📄 License

[Add your project license here, e.g., MIT, GPL, etc.]

## 📞 Contact

For any questions or suggestions, please contact the repository owner.

*   **GitHub:** [ayoub21dev](https://github.com/ayoub21dev)
*   **Project Link:** [https://github.com/ayoub21dev/ZenBladi-V2](https://github.com/ayoub21dev/ZenBladi-V2)

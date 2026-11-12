# BarangayGo — VS Code Project

## Project structure

- `index.html` — Home page
- `login.html` — Resident/admin login
- `register.html` — Resident registration
- `resident.html` — Resident dashboard
- `admin.html` — Admin dashboard
- `css/style.css` — Website styling
- `js/app.js` — Front-end JavaScript
- `backend/` — PHP API files
- `database/barangaygo.sql` — MySQL database setup
- `uploads/` — Uploaded valid ID files

## Requirements

1. Visual Studio Code
2. XAMPP (Apache, PHP, and MySQL)
3. A web browser

## Setup in XAMPP

1. Extract the `BarangayGo_VSCode.zip` file.
2. Copy the `BarangayGo_VSCode` folder into your XAMPP `htdocs` folder.
   - Usually: `C:\xampp\htdocs\BarangayGo_VSCode`
3. Open XAMPP Control Panel.
4. Start **Apache** and **MySQL**.
5. Open phpMyAdmin: `http://localhost/phpmyadmin`
6. Select **Import**, choose `database/barangaygo.sql`, and import it.
7. Open the project folder in VS Code:
   - VS Code → File → Open Folder → `BarangayGo_VSCode`
8. Visit the website:
   - `http://localhost/BarangayGo_VSCode/`

## Create the administrator account

The supplied `backend/create_admin.php` creates a default administrator account.

1. Make sure Apache and MySQL are running and the database has been imported.
2. Visit `http://localhost/BarangayGo_VSCode/backend/create_admin.php` once.
3. The default login details in the supplied source are:
   - Mobile: `09170000000`
   - Password: `ChangeMe123!`
4. **Immediately delete `backend/create_admin.php` after the account is created.**
5. Change the default password as soon as possible.

## Database configuration

The supplied `backend/config.php` is configured for a typical local XAMPP setup:

- Host: `localhost`
- Database: `barangaygo`
- Username: `root`
- Password: blank

If your MySQL settings differ, edit those values in `backend/config.php`.

## Important notes

- Do not open the PHP pages using `file://` or rely on VS Code Live Server. Use the Apache URL above so PHP can run.
- Keep the `uploads` folder writable by the PHP/Apache process.
- The `uploads` folder is included with a placeholder file; uploaded ID files will be saved there.
- This is a local development setup. Before public deployment, configure secure database credentials, HTTPS, file access controls, and remove the default admin-creation script.

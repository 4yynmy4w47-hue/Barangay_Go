const API = "backend/";

let csrfToken = "";

async function apiRequest(url, options = {}) {
    options.credentials = "same-origin";
    options.headers = options.headers || {};

    if (csrfToken) {
        options.headers["X-CSRF-Token"] = csrfToken;
    }

    const response = await fetch(API + url, options);

    const data = await response.json();

    if (!response.ok) {
        throw new Error(data.error || "Something went wrong.");
    }

    return data;
}

function showMessage(message, isError = false) {
    const element = document.getElementById("message");

    if (element) {
        element.textContent = message;
        element.style.color = isError ? "red" : "green";
    }
}

async function checkSession() {
    try {
        const data = await apiRequest("session.php");

        if (!data.loggedIn) {
            if (
                location.pathname.endsWith("resident.html") ||
                location.pathname.endsWith("admin.html")
            ) {
                location.href = "login.html";
            }
            return null;
        }

        csrfToken = data.csrf;

        if (
            location.pathname.endsWith("resident.html") &&
            data.user.role !== "resident"
        ) {
            location.href = "admin.html";
            return null;
        }

        if (
            location.pathname.endsWith("admin.html") &&
            data.user.role !== "admin"
        ) {
            location.href = "resident.html";
            return null;
        }

        return data.user;

    } catch (error) {
        console.error(error);
        return null;
    }
}

// LOGIN

const loginForm = document.getElementById("loginForm");

if (loginForm) {
    loginForm.addEventListener("submit", async function(event) {
        event.preventDefault();

        const formData = new FormData(loginForm);

        try {
            const data = await apiRequest("login.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    mobile: formData.get("mobile"),
                    password: formData.get("password")
                })
            });

            location.href =
                data.role === "admin" ? "admin.html" : "resident.html";

        } catch (error) {
            showMessage(error.message, true);
        }
    });
}

// REGISTER

const registerForm = document.getElementById("registerForm");

if (registerForm) {
    registerForm.addEventListener("submit", async function(event) {
        event.preventDefault();

        const formData = new FormData(registerForm);

        try {
            const data = await apiRequest("register.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    fullname: formData.get("fullname"),
                    mobile: formData.get("mobile"),
                    email: formData.get("email"),
                    address: formData.get("address"),
                    password: formData.get("password")
                })
            });

            showMessage(data.message);
            registerForm.reset();

        } catch (error) {
            showMessage(error.message, true);
        }
    });
}

// LOGOUT

async function logout() {
    try {
        await apiRequest("logout.php", {
            method: "POST"
        });

        location.href = "login.html";

    } catch (error) {
        alert(error.message);
    }
}

// RESIDENT DASHBOARD

async function loadResidentDashboard() {
    const user = await checkSession();

    if (!user || user.role !== "resident") return;

    document.getElementById("userName").textContent = user.fullname;

    await loadResidentRequests();
    await loadNotifications();
}

const requestForm = document.getElementById("requestForm");

if (requestForm) {
    requestForm.addEventListener("submit", async function(event) {
        event.preventDefault();

        try {
            const formData = new FormData(requestForm);

            const data = await apiRequest("requests.php", {
                method: "POST",
                body: formData
            });

            showMessage(data.message);
            requestForm.reset();

            await loadResidentRequests();
            await loadNotifications();

        } catch (error) {
            showMessage(error.message, true);
        }
    });
}

async function loadResidentRequests() {
    try {
        const requests = await apiRequest("requests.php");

        const table = document.getElementById("requestTable");

        table.replaceChildren();

        requests.forEach(request => {
            const row = document.createElement("tr");

            [
                request.reference_no,
                request.document_type,
                request.purpose,
                request.status,
                request.created_at
            ].forEach(value => {
                const cell = document.createElement("td");
                cell.textContent = value;
                row.appendChild(cell);
            });

            table.appendChild(row);
        });

    } catch (error) {
        console.error(error);
    }
}

async function loadNotifications() {
    try {
        const notifications = await apiRequest("notifications.php");

        const list = document.getElementById("notificationList");

        list.replaceChildren();

        notifications.forEach(notification => {
            const item = document.createElement("div");
            item.className = "notification";
            item.textContent = notification.message;
            list.appendChild(item);
        });

        if (notifications.length === 0) {
            list.textContent = "No notifications yet.";
        }

    } catch (error) {
        console.error(error);
    }
}

// ADMIN DASHBOARD

async function loadAdminDashboard() {
    const user = await checkSession();

    if (!user || user.role !== "admin") return;

    await loadAdminRequests();
}

async function loadAdminRequests() {
    try {
        const requests = await apiRequest("requests.php");

        const table = document.getElementById("adminRequestTable");

        table.replaceChildren();

        requests.forEach(request => {
            const row = document.createElement("tr");

            const values = [
                request.reference_no,
                request.fullname,
                request.mobile,
                request.document_type,
                request.purpose,
                request.created_at
            ];

            values.forEach(value => {
                const cell = document.createElement("td");
                cell.textContent = value;
                row.appendChild(cell);
            });

            const statusCell = document.createElement("td");

            const select = document.createElement("select");

            [
                "Pending",
                "Under Review",
                "Approved",
                "Rejected",
                "Ready for Pickup",
                "Completed"
            ].forEach(status => {
                const option = document.createElement("option");
                option.value = status;
                option.textContent = status;
                option.selected = status === request.status;
                select.appendChild(option);
            });

            statusCell.appendChild(select);
            row.appendChild(statusCell);

            const actionCell = document.createElement("td");
            const button = document.createElement("button");

            button.textContent = "Update";

            button.addEventListener("click", async function() {
                try {
                    const data = await apiRequest("requests.php", {
                        method: "PATCH",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            id: request.id,
                            status: select.value
                        })
                    });

                    alert(data.message);
                    await loadAdminRequests();

                } catch (error) {
                    alert(error.message);
                }
            });

            actionCell.appendChild(button);
            row.appendChild(actionCell);

            table.appendChild(row);
        });

    } catch (error) {
        console.error(error);
    }
}

// START DASHBOARD

if (document.getElementById("requestForm")) {
    loadResidentDashboard();
}

if (document.getElementById("adminRequestTable")) {
    loadAdminDashboard();
}

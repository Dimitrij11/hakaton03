<!DOCTYPE html>
<html>

<head>
    <title>Admin Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            margin-top: 0;
        }

        pre {
            background-color: #f8f8f8;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }

        .endpoints {
            margin-top: 20px;
        }

        .endpoint {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f0f8ff;
            border-left: 3px solid #007bff;
        }

        .method {
            font-weight: bold;
            display: inline-block;
            min-width: 60px;
        }

        .get {
            color: #28a745;
        }

        .post {
            color: #007bff;
        }

        .put {
            color: #fd7e14;
        }

        .delete {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Admin API Documentation</h1>

        <p>This page provides documentation for the Admin API endpoints that the frontend team can integrate with.</p>

        <h2>Authentication</h2>
        <p>All admin API endpoints require authentication. You need to:</p>
        <ol>
            <li>Login using <code>/login</code> endpoint to get a token</li>
            <li>Include the token in all API requests in Authorization header: <code>Authorization: Bearer
                    YOUR_TOKEN</code></li>
        </ol>

        <pre>
// Login example
fetch('/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        email: 'admin@example.com',
        password: 'password'
    })
})
.then(response => response.json())
.then(data => {
    // Store token
    localStorage.setItem('token', data.token);
});
        </pre>

        <h2>API Base URL</h2>
        <p>All admin endpoints are prefixed with: <code>/api/admin</code></p>

        <h2>Available Endpoints</h2>

        <div class="endpoints">
            <h3>Dashboard</h3>
            <div class="endpoint">
                <span class="method get">GET</span> <code>/api/admin/dashboard</code> - Get dashboard statistics
            </div>

            <h3>Users</h3>
            <div class="endpoint">
                <span class="method get">GET</span> <code>/api/admin/users</code> - List all users (paginated)
            </div>
            <div class="endpoint">
                <span class="method get">GET</span> <code>/api/admin/users/{id}</code> - Get user details
            </div>
            <div class="endpoint">
                <span class="method put">PUT</span> <code>/api/admin/users/{id}</code> - Update user
            </div>
            <div class="endpoint">
                <span class="method delete">DELETE</span> <code>/api/admin/users/{id}</code> - Delete user
            </div>

            <h3>Categories</h3>
            <div class="endpoint">
                <span class="method get">GET</span> <code>/api/admin/categories</code> - List all categories
            </div>
            <div class="endpoint">
                <span class="method get">GET</span> <code>/api/admin/categories/{id}</code> - Get category details
            </div>
            <div class="endpoint">
                <span class="method post">POST</span> <code>/api/admin/categories</code> - Create new category
            </div>
            <div class="endpoint">
                <span class="method put">PUT</span> <code>/api/admin/categories/{id}</code> - Update category
            </div>
            <div class="endpoint">
                <span class="method delete">DELETE</span> <code>/api/admin/categories/{id}</code> - Delete category
            </div>

            <h3>Courses</h3>
            <div class="endpoint">
                <span class="method get">GET</span> <code>/api/admin/courses</code> - List all courses (paginated)
            </div>
            <div class="endpoint">
                <span class="method get">GET</span> <code>/api/admin/courses/{id}</code> - Get course details with
                modules and lessons
            </div>
            <div class="endpoint">
                <span class="method post">POST</span> <code>/api/admin/courses</code> - Create new course
            </div>
            <div class="endpoint">
                <span class="method put">PUT</span> <code>/api/admin/courses/{id}</code> - Update course
            </div>
            <div class="endpoint">
                <span class="method delete">DELETE</span> <code>/api/admin/courses/{id}</code> - Delete course
            </div>
            <div class="endpoint">
                <span class="method get">GET</span> <code>/api/admin/available-professors</code> - Get list of available
                professors
            </div>

            <h3>Modules</h3>
            <div class="endpoint">
                <span class="method get">GET</span> <code>/api/admin/modules?course_id={course_id}</code> - List modules
                for a course
            </div>
            <div class="endpoint">
                <span class="method get">GET</span> <code>/api/admin/modules/{id}</code> - Get module details
            </div>
            <div class="endpoint">
                <span class="method post">POST</span> <code>/api/admin/modules</code> - Create new module
            </div>
            <div class="endpoint">
                <span class="method put">PUT</span> <code>/api/admin/modules/{id}</code> - Update module
            </div>
            <div class="endpoint">
                <span class="method delete">DELETE</span> <code>/api/admin/modules/{id}</code> - Delete module
            </div>
            <div class="endpoint">
                <span class="method post">POST</span> <code>/api/admin/modules/reorder</code> - Reorder modules
            </div>

            <h3>Lessons</h3>
            <div class="endpoint">
                <span class="method get">GET</span> <code>/api/admin/lessons?module_id={module_id}</code> - List lessons
                for a module
            </div>
            <div class="endpoint">
                <span class="method get">GET</span> <code>/api/admin/lessons/{id}</code> - Get lesson details
            </div>
            <div class="endpoint">
                <span class="method post">POST</span> <code>/api/admin/lessons</code> - Create new lesson
            </div>
            <div class="endpoint">
                <span class="method put">PUT</span> <code>/api/admin/lessons/{id}</code> - Update lesson
            </div>
            <div class="endpoint">
                <span class="method delete">DELETE</span> <code>/api/admin/lessons/{id}</code> - Delete lesson
            </div>
            <div class="endpoint">
                <span class="method post">POST</span> <code>/api/admin/lessons/reorder</code> - Reorder lessons
            </div>
        </div>
    </div>
</body>

</html>

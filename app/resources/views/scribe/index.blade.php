<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Laravel API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://localhost:8000";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.3.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.3.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-admin" class="tocify-header">
                <li class="tocify-item level-1" data-unique="admin">
                    <a href="#admin">Admin</a>
                </li>
                                    <ul id="tocify-subheader-admin" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="admin-GETapi-admin-users">
                                <a href="#admin-GETapi-admin-users">List Admin and Super Admin Users

Retrieves a paginated list of users who have either the `admin` or `super-admin` role.
Only accessible by users with the `super-admin` role.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="admin-POSTapi-admin-users-register">
                                <a href="#admin-POSTapi-admin-users-register">Admin Registration

Creates a new admin user and assigns the specified roles.
Only accessible to users with the `super-admin` role.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="admin-POSTapi-admin-users-update-roles--userId-">
                                <a href="#admin-POSTapi-admin-users-update-roles--userId-">Update User Roles

Updates (synchronizes) the roles assigned to a specific user.
This operation replaces all existing roles with the provided list.
Only accessible to users with the `super-admin` role.
The system prevents removing the last remaining `super-admin`.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-customer" class="tocify-header">
                <li class="tocify-item level-1" data-unique="customer">
                    <a href="#customer">Customer</a>
                </li>
                                    <ul id="tocify-subheader-customer" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="customer-POSTapi-user-register">
                                <a href="#customer-POSTapi-user-register">User Registration

Creates a new customer account and returns an API token.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="customer-GETapi-user-company">
                                <a href="#customer-GETapi-user-company">Get company profile</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="customer-POSTapi-user-company">
                                <a href="#customer-POSTapi-user-company">Upsert company profile</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="customer-GETapi-user-users">
                                <a href="#customer-GETapi-user-users">List company users</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="customer-POSTapi-user-users">
                                <a href="#customer-POSTapi-user-users">Create a company sub-user</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="customer-DELETEapi-user-users--userId-">
                                <a href="#customer-DELETEapi-user-users--userId-">Remove a company sub-user</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: October 18, 2025</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>

        <h1 id="admin">Admin</h1>

    

                                <h2 id="admin-GETapi-admin-users">List Admin and Super Admin Users

Retrieves a paginated list of users who have either the `admin` or `super-admin` role.
Only accessible by users with the `super-admin` role.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-admin-users">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/admin/users?q=sylwia&amp;per_page=10" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/admin/users"
);

const params = {
    "q": "sylwia",
    "per_page": "10",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-admin-users">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Sylwia&quot;,
            &quot;email&quot;: &quot;sylwia@example.com&quot;,
            &quot;roles&quot;: [
                &quot;admin&quot;
            ]
        },
        {
            &quot;id&quot;: 2,
            &quot;name&quot;: &quot;John Doe&quot;,
            &quot;email&quot;: &quot;john@example.com&quot;,
            &quot;roles&quot;: [
                &quot;super-admin&quot;,
                &quot;admin&quot;
            ]
        }
    ],
    &quot;current_page&quot;: 1,
    &quot;per_page&quot;: 25,
    &quot;total&quot;: 2
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-admin-users" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-admin-users"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-admin-users"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-admin-users" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-admin-users">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-admin-users" data-method="GET"
      data-path="api/admin/users"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-admin-users', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-admin-users"
                    onclick="tryItOut('GETapi-admin-users');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-admin-users"
                    onclick="cancelTryOut('GETapi-admin-users');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-admin-users"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/admin/users</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-admin-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-admin-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>q</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="q"                data-endpoint="GETapi-admin-users"
               value="sylwia"
               data-component="query">
    <br>
<p>Filter results by name or email. Example: <code>sylwia</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>per_page</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="number" style="display: none"
               step="any"               name="per_page"                data-endpoint="GETapi-admin-users"
               value="10"
               data-component="query">
    <br>
<p>The number of results per page (1–100). Default: 25. Example: <code>10</code></p>
            </div>
                </form>

                    <h2 id="admin-POSTapi-admin-users-register">Admin Registration

Creates a new admin user and assigns the specified roles.
Only accessible to users with the `super-admin` role.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-admin-users-register">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/admin/users/register" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Anna Nowak\",
    \"email\": \"anna.admin@example.com\",
    \"password\": \"test1234\",
    \"password_confirmation\": \"test1234\",
    \"roles\": [
        \"admin\"
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/admin/users/register"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Anna Nowak",
    "email": "anna.admin@example.com",
    "password": "test1234",
    "password_confirmation": "test1234",
    "roles": [
        "admin"
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-admin-users-register">
            <blockquote>
            <p>Example response (201):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: 15,
    &quot;email&quot;: &quot;anna.admin@example.com&quot;,
    &quot;roles&quot;: [
        &quot;admin&quot;
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-admin-users-register" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-admin-users-register"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-admin-users-register"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-admin-users-register" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-admin-users-register">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-admin-users-register" data-method="POST"
      data-path="api/admin/users/register"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-admin-users-register', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-admin-users-register"
                    onclick="tryItOut('POSTapi-admin-users-register');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-admin-users-register"
                    onclick="cancelTryOut('POSTapi-admin-users-register');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-admin-users-register"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/admin/users/register</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-admin-users-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-admin-users-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-admin-users-register"
               value="Anna Nowak"
               data-component="body">
    <br>
<p>The admin user's full name. Example: <code>Anna Nowak</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-admin-users-register"
               value="anna.admin@example.com"
               data-component="body">
    <br>
<p>A unique email address for the admin. Example: <code>anna.admin@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-admin-users-register"
               value="test1234"
               data-component="body">
    <br>
<p>The account password (minimum 8 characters). Example: <code>test1234</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password_confirmation</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password_confirmation"                data-endpoint="POSTapi-admin-users-register"
               value="test1234"
               data-component="body">
    <br>
<p>Password confirmation. Example: <code>test1234</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>roles</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="roles[0]"                data-endpoint="POSTapi-admin-users-register"
               data-component="body">
        <input type="text" style="display: none"
               name="roles[1]"                data-endpoint="POSTapi-admin-users-register"
               data-component="body">
    <br>
<p>optional A list of roles to assign to the user.</p>
        </div>
        </form>

                    <h2 id="admin-POSTapi-admin-users-update-roles--userId-">Update User Roles

Updates (synchronizes) the roles assigned to a specific user.
This operation replaces all existing roles with the provided list.
Only accessible to users with the `super-admin` role.
The system prevents removing the last remaining `super-admin`.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-admin-users-update-roles--userId-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/admin/users/update/roles/7" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"roles\": [
        \"admin\",
        \"manager\"
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/admin/users/update/roles/7"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "roles": [
        "admin",
        "manager"
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-admin-users-update-roles--userId-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: 7,
    &quot;roles&quot;: [
        &quot;admin&quot;,
        &quot;manager&quot;
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Cannot remove the super-admin role. This is the last super-admin.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-admin-users-update-roles--userId-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-admin-users-update-roles--userId-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-admin-users-update-roles--userId-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-admin-users-update-roles--userId-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-admin-users-update-roles--userId-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-admin-users-update-roles--userId-" data-method="POST"
      data-path="api/admin/users/update/roles/{userId}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-admin-users-update-roles--userId-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-admin-users-update-roles--userId-"
                    onclick="tryItOut('POSTapi-admin-users-update-roles--userId-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-admin-users-update-roles--userId-"
                    onclick="cancelTryOut('POSTapi-admin-users-update-roles--userId-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-admin-users-update-roles--userId-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/admin/users/update/roles/{userId}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-admin-users-update-roles--userId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-admin-users-update-roles--userId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>userId</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="userId"                data-endpoint="POSTapi-admin-users-update-roles--userId-"
               value="7"
               data-component="url">
    <br>
<p>The ID of the user whose roles are being updated. Example: <code>7</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>roles</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="roles[0]"                data-endpoint="POSTapi-admin-users-update-roles--userId-"
               data-component="body">
        <input type="text" style="display: none"
               name="roles[1]"                data-endpoint="POSTapi-admin-users-update-roles--userId-"
               data-component="body">
    <br>
<p>A list of roles to assign to the user.</p>
        </div>
        </form>

                <h1 id="customer">Customer</h1>

    

                                <h2 id="customer-POSTapi-user-register">User Registration

Creates a new customer account and returns an API token.</h2>

<p>
</p>



<span id="example-requests-POSTapi-user-register">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/user/register" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"John Doe\",
    \"email\": \"john@example.com\",
    \"password\": \"test1234\",
    \"password_confirmation\": \"test1234\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/user/register"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "test1234",
    "password_confirmation": "test1234"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-user-register">
            <blockquote>
            <p>Example response (201):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The account has been successfully created.&quot;,
    &quot;user&quot;: {
        &quot;id&quot;: 12,
        &quot;name&quot;: &quot;John Doe&quot;,
        &quot;email&quot;: &quot;john@example.com&quot;,
        &quot;roles&quot;: [
            &quot;customer&quot;
        ]
    },
    &quot;token&quot;: &quot;1|abc123xyz...&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-user-register" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-user-register"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-user-register"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-user-register" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-user-register">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-user-register" data-method="POST"
      data-path="api/user/register"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-user-register', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-user-register"
                    onclick="tryItOut('POSTapi-user-register');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-user-register"
                    onclick="cancelTryOut('POSTapi-user-register');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-user-register"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/user/register</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-user-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-user-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-user-register"
               value="John Doe"
               data-component="body">
    <br>
<p>The user's full name. Example: <code>John Doe</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-user-register"
               value="john@example.com"
               data-component="body">
    <br>
<p>A unique email address. Example: <code>john@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-user-register"
               value="test1234"
               data-component="body">
    <br>
<p>The account password (minimum 8 characters). Example: <code>test1234</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password_confirmation</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password_confirmation"                data-endpoint="POSTapi-user-register"
               value="test1234"
               data-component="body">
    <br>
<p>Password confirmation. Example: <code>test1234</code></p>
        </div>
        </form>

                    <h2 id="customer-GETapi-user-company">Get company profile</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns the authenticated customer's company profile (if set).</p>

<span id="example-requests-GETapi-user-company">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/user/company" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/user/company"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-user-company">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;company_name&quot;: &quot;ACME Sp. z o.o.&quot;,
    &quot;vat_id&quot;: &quot;PL1234567890&quot;,
    &quot;regon&quot;: &quot;123456789&quot;,
    &quot;address_line1&quot;: &quot;ul. Prosta 1&quot;,
    &quot;address_line2&quot;: null,
    &quot;postal_code&quot;: &quot;00-000&quot;,
    &quot;city&quot;: &quot;Warszawa&quot;,
    &quot;country&quot;: &quot;PL&quot;,
    &quot;phone&quot;: &quot;+48 123 456 789&quot;,
    &quot;description&quot;: &quot;test&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-user-company" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-user-company"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-user-company"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-user-company" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-user-company">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-user-company" data-method="GET"
      data-path="api/user/company"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-user-company', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-user-company"
                    onclick="tryItOut('GETapi-user-company');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-user-company"
                    onclick="cancelTryOut('GETapi-user-company');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-user-company"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/user/company</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-user-company"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-user-company"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="customer-POSTapi-user-company">Upsert company profile</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Creates or updates the authenticated customer's company data (1:1 with user).
Returns the saved record.</p>

<span id="example-requests-POSTapi-user-company">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/user/company" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"company_name\": \"ACME Sp. z o.o.\",
    \"vat_id\": \"PL1234567890\",
    \"regon\": \"123456789\",
    \"address_line1\": \"ul. Prosta 1\",
    \"address_line2\": \"lok. 12\",
    \"postal_code\": \"00-000\",
    \"city\": \"Warszawa\",
    \"country\": \"PL\",
    \"phone\": \"+48 123 456 789\",
    \"description\": \"Qui commodi incidunt iure odit.\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/user/company"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "company_name": "ACME Sp. z o.o.",
    "vat_id": "PL1234567890",
    "regon": "123456789",
    "address_line1": "ul. Prosta 1",
    "address_line2": "lok. 12",
    "postal_code": "00-000",
    "city": "Warszawa",
    "country": "PL",
    "phone": "+48 123 456 789",
    "description": "Qui commodi incidunt iure odit."
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-user-company">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: 3,
    &quot;user_id&quot;: 12,
    &quot;company_name&quot;: &quot;ACME Sp. z o.o.&quot;,
    &quot;vat_id&quot;: &quot;PL1234567890&quot;,
    &quot;regon&quot;: &quot;123456789&quot;,
    &quot;address_line1&quot;: &quot;ul. Prosta 1&quot;,
    &quot;address_line2&quot;: null,
    &quot;postal_code&quot;: &quot;00-000&quot;,
    &quot;city&quot;: &quot;Warszawa&quot;,
    &quot;country&quot;: &quot;PL&quot;,
    &quot;phone&quot;: &quot;+48 123 456 789&quot;,
    &quot;description&quot;: &quot;test&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-user-company" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-user-company"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-user-company"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-user-company" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-user-company">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-user-company" data-method="POST"
      data-path="api/user/company"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-user-company', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-user-company"
                    onclick="tryItOut('POSTapi-user-company');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-user-company"
                    onclick="cancelTryOut('POSTapi-user-company');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-user-company"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/user/company</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-user-company"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-user-company"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>company_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="company_name"                data-endpoint="POSTapi-user-company"
               value="ACME Sp. z o.o."
               data-component="body">
    <br>
<p>The legal company name. Example: <code>ACME Sp. z o.o.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>vat_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="vat_id"                data-endpoint="POSTapi-user-company"
               value="PL1234567890"
               data-component="body">
    <br>
<p>The VAT ID (e.g., NIP). Example: <code>PL1234567890</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>regon</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="regon"                data-endpoint="POSTapi-user-company"
               value="123456789"
               data-component="body">
    <br>
<p>Optional business identifier. Example: <code>123456789</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>address_line1</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="address_line1"                data-endpoint="POSTapi-user-company"
               value="ul. Prosta 1"
               data-component="body">
    <br>
<p>Street and number. Example: <code>ul. Prosta 1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>address_line2</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="address_line2"                data-endpoint="POSTapi-user-company"
               value="lok. 12"
               data-component="body">
    <br>
<p>Apartment/extra details. Example: <code>lok. 12</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>postal_code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="postal_code"                data-endpoint="POSTapi-user-company"
               value="00-000"
               data-component="body">
    <br>
<p>Postal code. Example: <code>00-000</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>city</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="city"                data-endpoint="POSTapi-user-company"
               value="Warszawa"
               data-component="body">
    <br>
<p>City. Example: <code>Warszawa</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>country</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="country"                data-endpoint="POSTapi-user-company"
               value="PL"
               data-component="body">
    <br>
<p>2-letter ISO code. Default: PL. Example: <code>PL</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-user-company"
               value="+48 123 456 789"
               data-component="body">
    <br>
<p>Contact phone. Example: <code>+48 123 456 789</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="POSTapi-user-company"
               value="Qui commodi incidunt iure odit."
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>Qui commodi incidunt iure odit.</code></p>
        </div>
        </form>

                    <h2 id="customer-GETapi-user-users">List company users</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns a paginated list of users that belong to the same company as the requester.</p>

<span id="example-requests-GETapi-user-users">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/user/users?q=anna&amp;per_page=16" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/user/users"
);

const params = {
    "q": "anna",
    "per_page": "16",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-user-users">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 5,
            &quot;name&quot;: &quot;Anna&quot;,
            &quot;email&quot;: &quot;anna@acme.com&quot;,
            &quot;roles&quot;: [
                &quot;buyer&quot;
            ]
        }
    ],
    &quot;current_page&quot;: 1,
    &quot;per_page&quot;: 25,
    &quot;total&quot;: 1
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-user-users" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-user-users"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-user-users"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-user-users" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-user-users">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-user-users" data-method="GET"
      data-path="api/user/users"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-user-users', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-user-users"
                    onclick="tryItOut('GETapi-user-users');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-user-users"
                    onclick="cancelTryOut('GETapi-user-users');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-user-users"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/user/users</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-user-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-user-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>q</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="q"                data-endpoint="GETapi-user-users"
               value="anna"
               data-component="query">
    <br>
<p>Filter by name or email. Example: <code>anna</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>per_page</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="number" style="display: none"
               step="any"               name="per_page"                data-endpoint="GETapi-user-users"
               value="16"
               data-component="query">
    <br>
<p>Results per page (1–100). Default: 25 Example: <code>16</code></p>
            </div>
                </form>

                    <h2 id="customer-POSTapi-user-users">Create a company sub-user</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Creates a new user in the same company as the requester.
Default role: <code>buyer</code> (if none provided).
Only users with appropriate privileges (e.g., <code>customer-admin</code>) should access this endpoint.</p>

<span id="example-requests-POSTapi-user-users">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/user/users" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Anna Kowalska\",
    \"email\": \"anna@acme.com\",
    \"password\": \"Test1234!\",
    \"password_confirmation\": \"Test1234!\",
    \"roles\": [
        \"buyer\"
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/user/users"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Anna Kowalska",
    "email": "anna@acme.com",
    "password": "Test1234!",
    "password_confirmation": "Test1234!",
    "roles": [
        "buyer"
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-user-users">
            <blockquote>
            <p>Example response (201):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: 21,
    &quot;email&quot;: &quot;anna@acme.com&quot;,
    &quot;roles&quot;: [
        &quot;buyer&quot;
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-user-users" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-user-users"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-user-users"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-user-users" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-user-users">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-user-users" data-method="POST"
      data-path="api/user/users"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-user-users', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-user-users"
                    onclick="tryItOut('POSTapi-user-users');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-user-users"
                    onclick="cancelTryOut('POSTapi-user-users');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-user-users"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/user/users</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-user-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-user-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-user-users"
               value="Anna Kowalska"
               data-component="body">
    <br>
<p>Full name. Example: <code>Anna Kowalska</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-user-users"
               value="anna@acme.com"
               data-component="body">
    <br>
<p>Unique email. Example: <code>anna@acme.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-user-users"
               value="Test1234!"
               data-component="body">
    <br>
<p>Min 8 chars. Example: <code>Test1234!</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password_confirmation</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password_confirmation"                data-endpoint="POSTapi-user-users"
               value="Test1234!"
               data-component="body">
    <br>
<p>Example: <code>Test1234!</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>roles</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="roles[0]"                data-endpoint="POSTapi-user-users"
               data-component="body">
        <input type="text" style="display: none"
               name="roles[1]"                data-endpoint="POSTapi-user-users"
               data-component="body">
    <br>
<p>Roles to assign. Default: [&quot;buyer&quot;].</p>
        </div>
        </form>

                    <h2 id="customer-DELETEapi-user-users--userId-">Remove a company sub-user</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Deletes a user that belongs to the same company as the requester.
(Prevents deleting yourself.)</p>

<span id="example-requests-DELETEapi-user-users--userId-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/user/users/21" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/user/users/21"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-user-users--userId-">
            <blockquote>
            <p>Example response (204):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-user-users--userId-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-user-users--userId-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-user-users--userId-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-user-users--userId-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-user-users--userId-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-user-users--userId-" data-method="DELETE"
      data-path="api/user/users/{userId}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-user-users--userId-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-user-users--userId-"
                    onclick="tryItOut('DELETEapi-user-users--userId-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-user-users--userId-"
                    onclick="cancelTryOut('DELETEapi-user-users--userId-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-user-users--userId-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/user/users/{userId}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-user-users--userId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-user-users--userId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>userId</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="userId"                data-endpoint="DELETEapi-user-users--userId-"
               value="21"
               data-component="url">
    <br>
<p>The ID of the sub-user to delete. Example: <code>21</code></p>
            </div>
                    </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>

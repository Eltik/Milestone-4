<?php
    session_start();

    $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    // Route API requests to the app layer
    if (str_starts_with($uri, "/api")) {
        require_once __DIR__ . "/app/Index.php";
        exit;
    }

    $isLoggedIn = !empty($_SESSION["user_id"]);
?>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Ledgerline | Portfolio Overview</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-white text-gray-900 font-sans antialiased min-h-screen flex flex-col">

        <!-- Auth Screen (Login / Signup) -->
        <div id="auth-screen" class="<?php echo $isLoggedIn ? 'hidden' : ''; ?> min-h-screen flex items-center justify-center bg-gray-50">
            <div class="w-full max-w-md px-6">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center gap-2 mb-4">
                        <div class="bg-black text-white p-1.5 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                <path d="M5 21v-6" />
                                <path d="M12 21V3" />
                                <path d="M19 21V9" />
                            </svg>
                        </div>
                        <span class="text-lg tracking-tight font-medium text-black">Ledgerline</span>
                    </div>
                    <h1 id="auth-title" class="text-2xl font-medium text-black">Welcome back</h1>
                    <p id="auth-subtitle" class="text-sm text-gray-500 mt-1">Sign in to your account</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <div id="auth-error" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600"></div>

                    <!-- Login Form -->
                    <form id="login-form" onsubmit="handleLogin(event)">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" for="login-username">Username</label>
                                <input id="login-username" type="text" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"
                                    placeholder="Enter your username" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" for="login-password">Password</label>
                                <input id="login-password" type="password" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"
                                    placeholder="Enter your password" />
                            </div>
                            <button type="submit" id="login-btn"
                                class="w-full bg-black text-white text-sm font-medium py-2.5 rounded-lg hover:bg-gray-800 transition-colors">
                                Sign In
                            </button>
                        </div>
                    </form>

                    <!-- Signup Form -->
                    <form id="signup-form" class="hidden" onsubmit="handleSignup(event)">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" for="signup-email">Email</label>
                                <input id="signup-email" type="email" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"
                                    placeholder="you@example.com" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" for="signup-phone">Phone</label>
                                <input id="signup-phone" type="tel" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"
                                    placeholder="1234567890" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" for="signup-username">Username</label>
                                <input id="signup-username" type="text" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"
                                    placeholder="Choose a username" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" for="signup-password">Password</label>
                                <input id="signup-password" type="password" required minlength="6"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"
                                    placeholder="At least 6 characters" />
                            </div>
                            <button type="submit" id="signup-btn"
                                class="w-full bg-black text-white text-sm font-medium py-2.5 rounded-lg hover:bg-gray-800 transition-colors">
                                Create Account
                            </button>
                        </div>
                    </form>
                </div>
                <p class="text-center text-sm text-gray-500 mt-4">
                    <span id="auth-toggle-text">Don't have an account?</span>
                    <button id="auth-toggle-btn" onclick="toggleAuthMode()" class="font-medium text-black hover:underline ml-1">Sign up</button>
                </p>
            </div>
        </div>

        <!-- Dashboard (shown when logged in) -->
        <div id="dashboard" class="<?php echo $isLoggedIn ? '' : 'hidden'; ?> flex flex-col min-h-screen">
            <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center gap-2">
                            <div class="bg-black text-white p-1.5 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                    <path d="M5 21v-6" />
                                    <path d="M12 21V3" />
                                    <path d="M19 21V9" />
                                </svg>
                            </div>
                            <span class="text-lg tracking-tight font-medium text-black">Ledgerline</span>
                        </div>
                        <nav class="hidden md:flex space-x-8">
                            <a href="#" class="text-sm font-medium text-black border-b-2 border-black px-1 py-5" data-nav="overview">Overview</a>
                            <a href="#" class="text-sm font-normal text-gray-500 hover:text-black transition-colors px-1 py-5" data-nav="integrations">Integrations</a>
                            <a href="#" class="text-sm font-normal text-gray-500 hover:text-black transition-colors px-1 py-5" data-nav="transactions">Transactions</a>
                        </nav>
                        <div class="flex items-center gap-4">
                            <span id="user-greeting" class="text-sm text-gray-500 hidden md:inline"></span>
                            <button class="text-gray-400 hover:text-black transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                    <path d="M10.268 21a2 2 0 0 0 3.464 0" />
                                    <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326" />
                                </svg>
                            </button>
                            <div onclick="handleLogout()" class="h-8 w-8 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden cursor-pointer" title="Sign out">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <main class="flex-grow">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
                    <!-- Net Worth Summary -->
                    <div class="mb-10">
                        <h1 class="text-sm font-medium text-gray-500 mb-1">Total Net Worth</h1>
                        <div class="flex items-baseline gap-3">
                            <h2 id="net-worth" class="text-4xl tracking-tight font-medium text-black">--</h2>
                            <span id="net-worth-change" class="inline-flex items-center gap-1 text-sm font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                    <path d="M16 7h6v6" />
                                    <path d="m22 7-8.5 8.5-5-5L2 17" />
                                </svg>
                                <span id="net-worth-change-text">Loading...</span>
                            </span>
                        </div>
                        <p id="last-updated" class="text-xs text-gray-400 mt-2"></p>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left Column: Connected Accounts -->
                        <div class="lg:col-span-1 space-y-6">
                            <div>
                                <h3 class="text-base tracking-tight font-medium text-black mb-4">Connected Accounts</h3>
                                <div class="space-y-3">
                                    <!-- Robinhood Card -->
                                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:border-gray-300 transition-colors">
                                        <div class="flex justify-between items-start mb-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center text-green-600 border border-green-100">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                                        <rect width="14" height="20" x="5" y="2" rx="2" ry="2" />
                                                        <path d="M12 18h.01" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-medium text-black">Robinhood</h4>
                                                    <p id="rh-summary" class="text-xs text-gray-500">Brokerage • Loading...</p>
                                                </div>
                                            </div>
                                            <span class="flex h-2 w-2 relative mt-1.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                            <span class="text-xs text-gray-400">Syncing live data...</span>
                                            <button class="text-xs font-medium text-gray-500 hover:text-black transition-colors">Manage</button>
                                        </div>
                                    </div>
                                    <!-- Yahoo Finance Card -->
                                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:border-gray-300 transition-colors">
                                        <div class="flex justify-between items-start mb-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600 border border-purple-100">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                                        <circle cx="12" cy="12" r="10" />
                                                        <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" />
                                                        <path d="M2 12h20" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-medium text-black">Yahoo Finance</h4>
                                                    <p id="yf-summary" class="text-xs text-gray-500">Portfolio • Loading...</p>
                                                </div>
                                            </div>
                                            <span class="flex h-2 w-2 relative mt-1.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                            <span class="text-xs text-gray-400">Syncing live data...</span>
                                            <button class="text-xs font-medium text-gray-500 hover:text-black transition-colors">Manage</button>
                                        </div>
                                    </div>
                                    <!-- Add Source Button -->
                                    <button class="w-full bg-transparent border border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-gray-400 hover:bg-gray-50 transition-all flex flex-col items-center justify-center gap-2 text-gray-500 h-28">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                            <path d="M5 12h14" />
                                            <path d="M12 5v14" />
                                        </svg>
                                        <span class="text-sm font-medium">Add data source</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Right Column: Holdings Table -->
                        <div class="lg:col-span-2">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base tracking-tight font-medium text-black">All Holdings</h3>
                                <div class="flex items-center gap-2">
                                    <button id="filter-all" class="text-xs font-medium px-3 py-1.5 rounded-lg bg-black text-white" onclick="filterHoldings('all')">All</button>
                                    <button id="filter-robinhood" class="text-xs font-medium px-3 py-1.5 rounded-lg text-gray-500 hover:bg-gray-100" onclick="filterHoldings('robinhood')">Robinhood</button>
                                    <button id="filter-yahoo" class="text-xs font-medium px-3 py-1.5 rounded-lg text-gray-500 hover:bg-gray-100" onclick="filterHoldings('yahoo')">Yahoo</button>
                                </div>
                            </div>
                            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-gray-100">
                                            <th class="text-left text-xs font-medium text-gray-500 px-4 py-3">Symbol</th>
                                            <th class="text-left text-xs font-medium text-gray-500 px-4 py-3">Name</th>
                                            <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">Price</th>
                                            <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">Qty</th>
                                            <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">Value</th>
                                            <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">Gain/Loss</th>
                                            <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">Source</th>
                                        </tr>
                                    </thead>
                                    <tbody id="holdings-table">
                                        <tr>
                                            <td colspan="7" class="text-center text-sm text-gray-400 px-4 py-8">Loading holdings...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Market Data -->
                            <div class="mt-8">
                                <h3 class="text-base tracking-tight font-medium text-black mb-4">Market Overview</h3>
                                <div id="market-grid" class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <div class="text-center text-sm text-gray-400 col-span-full py-8">Loading market data...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="bg-white border-t border-gray-200 mt-auto">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-gray-400">&copy; 2024 Ledgerline Inc. All rights reserved.</p>
                    <div class="flex gap-4">
                        <a href="#" class="text-xs text-gray-400 hover:text-black transition-colors">Security</a>
                        <a href="#" class="text-xs text-gray-400 hover:text-black transition-colors">Terms</a>
                        <a href="#" onclick="handleLogout()" class="text-xs text-gray-400 hover:text-black transition-colors cursor-pointer">Sign Out</a>
                    </div>
                </div>
            </footer>
        </div>

        <script>
            const fmt = (n) => n.toLocaleString("en-US", { style: "currency", currency: "USD" });
            const fmtPct = (n) => (n >= 0 ? "+" : "") + n.toFixed(2) + "%";
            const fmtCompact = (n) => {
                if (n >= 1e12) return "$" + (n / 1e12).toFixed(2) + "T";
                if (n >= 1e9) return "$" + (n / 1e9).toFixed(2) + "B";
                if (n >= 1e6) return "$" + (n / 1e6).toFixed(2) + "M";
                return fmt(n);
            };

            let rhPortfolio = null;
            let yfPortfolio = null;
            let rhStocks = null;
            let yfStocks = null;
            let currentFilter = "all";
            let isSignup = false;

            // ---- Auth ----

            function showError(msg) {
                const el = document.getElementById("auth-error");
                el.textContent = msg;
                el.classList.remove("hidden");
            }

            function hideError() {
                document.getElementById("auth-error").classList.add("hidden");
            }

            function toggleAuthMode() {
                isSignup = !isSignup;
                hideError();
                document.getElementById("login-form").classList.toggle("hidden", isSignup);
                document.getElementById("signup-form").classList.toggle("hidden", !isSignup);
                document.getElementById("auth-title").textContent = isSignup ? "Create an account" : "Welcome back";
                document.getElementById("auth-subtitle").textContent = isSignup ? "Get started with Ledgerline" : "Sign in to your account";
                document.getElementById("auth-toggle-text").textContent = isSignup ? "Already have an account?" : "Don't have an account?";
                document.getElementById("auth-toggle-btn").textContent = isSignup ? "Sign in" : "Sign up";
            }

            async function handleLogin(e) {
                e.preventDefault();
                hideError();
                const btn = document.getElementById("login-btn");
                btn.disabled = true;
                btn.textContent = "Signing in...";

                try {
                    const res = await fetch("/api/auth/login", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            username: document.getElementById("login-username").value,
                            password: document.getElementById("login-password").value
                        })
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        showError(data.error || "Login failed");
                        btn.disabled = false;
                        btn.textContent = "Sign In";
                        return;
                    }

                    showDashboard(data.user);
                } catch (err) {
                    showError("Network error. Please try again.");
                    btn.disabled = false;
                    btn.textContent = "Sign In";
                }
            }

            async function handleSignup(e) {
                e.preventDefault();
                hideError();
                const btn = document.getElementById("signup-btn");
                btn.disabled = true;
                btn.textContent = "Creating account...";

                try {
                    const res = await fetch("/api/auth/signup", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            email: document.getElementById("signup-email").value,
                            phone: document.getElementById("signup-phone").value,
                            username: document.getElementById("signup-username").value,
                            password: document.getElementById("signup-password").value
                        })
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        showError(data.error || "Signup failed");
                        btn.disabled = false;
                        btn.textContent = "Create Account";
                        return;
                    }

                    showDashboard(data.user);
                } catch (err) {
                    showError("Network error. Please try again.");
                    btn.disabled = false;
                    btn.textContent = "Create Account";
                }
            }

            async function handleLogout() {
                await fetch("/api/auth/logout", { method: "POST" });
                document.getElementById("dashboard").classList.add("hidden");
                document.getElementById("auth-screen").classList.remove("hidden");
                // Reset forms
                document.getElementById("login-form").reset();
                document.getElementById("signup-form").reset();
                hideError();
            }

            function showDashboard(user) {
                document.getElementById("auth-screen").classList.add("hidden");
                document.getElementById("dashboard").classList.remove("hidden");
                document.getElementById("user-greeting").textContent = "Hi, " + user.username;
                fetchData();
            }

            // ---- Dashboard ----

            async function fetchData() {
                const [rhP, yfP, rhS, yfS] = await Promise.all([
                    fetch("/api/sources/robinhood/portfolio").then(r => r.json()),
                    fetch("/api/sources/yahoo/portfolio").then(r => r.json()),
                    fetch("/api/sources/robinhood/stocks").then(r => r.json()),
                    fetch("/api/sources/yahoo/stocks").then(r => r.json())
                ]);

                rhPortfolio = rhP;
                yfPortfolio = yfP;
                rhStocks = rhS;
                yfStocks = yfS;

                renderSummary();
                renderHoldings();
                renderMarket();
            }

            function renderSummary() {
                const rhValue = rhPortfolio.account.portfolio_value;
                const yfValue = yfPortfolio.account.totalMarketValue;
                const total = rhValue + yfValue;

                const rhGain = rhPortfolio.account.total_return;
                const yfGain = yfPortfolio.account.totalGainLoss;
                const totalGain = rhGain + yfGain;
                const totalPct = (totalGain / (total - totalGain)) * 100;

                document.getElementById("net-worth").textContent = fmt(total);
                document.getElementById("net-worth-change-text").textContent =
                    (totalGain >= 0 ? "+" : "") + fmt(totalGain) + " (" + fmtPct(totalPct).replace("+", "") + ")";
                document.getElementById("last-updated").textContent = "Last updated just now";

                document.getElementById("rh-summary").textContent = "Brokerage \u2022 " + fmt(rhValue);
                document.getElementById("yf-summary").textContent = "Portfolio \u2022 " + fmt(yfValue);
            }

            function renderHoldings() {
                const tbody = document.getElementById("holdings-table");
                const rows = [];

                if (currentFilter === "all" || currentFilter === "robinhood") {
                    for (const h of rhPortfolio.holdings) {
                        rows.push({
                            symbol: h.symbol,
                            name: h.name,
                            price: h.current_price,
                            qty: h.quantity,
                            value: h.total_value,
                            gainLoss: h.gain_loss,
                            gainLossPct: h.gain_loss_percent,
                            source: "Robinhood"
                        });
                    }
                }

                if (currentFilter === "all" || currentFilter === "yahoo") {
                    for (const h of yfPortfolio.holdings) {
                        rows.push({
                            symbol: h.symbol,
                            name: h.shortName,
                            price: h.regularMarketPrice,
                            qty: h.sharesOwned,
                            value: h.marketValue,
                            gainLoss: h.gainLoss,
                            gainLossPct: h.gainLossPercent,
                            source: "Yahoo"
                        });
                    }
                }

                rows.sort((a, b) => b.value - a.value);

                tbody.innerHTML = rows.map(r => `
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-black">${r.symbol}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">${r.name}</td>
                        <td class="px-4 py-3 text-sm text-right text-black">${fmt(r.price)}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">${r.qty}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium text-black">${fmt(r.value)}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium ${r.gainLoss >= 0 ? 'text-emerald-600' : 'text-red-500'}">
                            ${(r.gainLoss >= 0 ? '+' : '') + fmt(r.gainLoss)} (${fmtPct(r.gainLossPct)})
                        </td>
                        <td class="px-4 py-3 text-xs text-right">
                            <span class="px-2 py-1 rounded-md ${r.source === 'Robinhood' ? 'bg-green-50 text-green-700' : 'bg-purple-50 text-purple-700'}">${r.source}</span>
                        </td>
                    </tr>
                `).join("");
            }

            function filterHoldings(filter) {
                currentFilter = filter;

                document.querySelectorAll("[id^='filter-']").forEach(btn => {
                    btn.className = "text-xs font-medium px-3 py-1.5 rounded-lg text-gray-500 hover:bg-gray-100";
                });
                document.getElementById("filter-" + filter).className = "text-xs font-medium px-3 py-1.5 rounded-lg bg-black text-white";

                renderHoldings();
            }

            function renderMarket() {
                const grid = document.getElementById("market-grid");
                const seen = new Set();
                const stocks = [];

                for (const s of rhStocks) {
                    stocks.push({ symbol: s.symbol, name: s.name, price: s.price, change: s.change, changePct: s.change_percent, volume: s.volume, marketCap: s.market_cap });
                    seen.add(s.symbol);
                }
                for (const s of yfStocks) {
                    if (!seen.has(s.symbol)) {
                        stocks.push({ symbol: s.symbol, name: s.shortName, price: s.regularMarketPrice, change: s.regularMarketChange, changePct: s.regularMarketChangePercent, volume: s.regularMarketVolume, marketCap: s.marketCap });
                    }
                }

                grid.innerHTML = stocks.map(s => `
                    <div class="bg-white border border-gray-200 rounded-xl p-4 hover:border-gray-300 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-black">${s.symbol}</span>
                            <span class="text-xs font-medium px-1.5 py-0.5 rounded ${s.change >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500'}">${fmtPct(s.changePct)}</span>
                        </div>
                        <div class="text-lg font-medium text-black">${fmt(s.price)}</div>
                        <div class="text-xs text-gray-400 mt-1">${s.name}</div>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                            <span class="text-xs text-gray-400">Vol: ${(s.volume / 1e6).toFixed(1)}M</span>
                            <span class="text-xs text-gray-400">Cap: ${fmtCompact(s.marketCap)}</span>
                        </div>
                    </div>
                `).join("");
            }

            // ---- Init ----

            async function init() {
                try {
                    const res = await fetch("/api/auth/me");
                    if (res.ok) {
                        const data = await res.json();
                        showDashboard(data.user);
                    }
                } catch (e) {
                    // Not logged in, auth screen already showing
                }
            }

            init();
        </script>
    </body>
</html>

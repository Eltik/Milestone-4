<?php
session_start();

require_once __DIR__ . "/database/index.php"; // This file should define $conn
require_once __DIR__ . "/database/impl/Connectors.php";

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

if (str_starts_with($uri, "/api")) {
    require_once __DIR__ . "/app/Index.php";
    exit;
}

$isLoggedIn = !empty($_SESSION["user_id"]);
$hasConnectors = false;

if ($isLoggedIn) {
    // This MUST use the current session's ID
    $userConnectors = \Database\Connectors::getConnectorsByUser($conn, $_SESSION["user_id"]);

    // Check if the array is actually empty
    $hasConnectors = (is_array($userConnectors) && count($userConnectors) > 0);
}
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
    <div id="auth-screen"
        class="<?php echo $isLoggedIn ? 'hidden' : ''; ?> min-h-screen flex items-center justify-center bg-gray-50">
        <div class="w-full max-w-md px-6">
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 mb-4">
                    <div class="bg-black text-white p-1.5 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                            class="w-5 h-5">
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
                <div id="auth-error"
                    class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600"></div>

                <!-- Login Form -->
                <form id="login-form" onsubmit="handleLogin(event)">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"
                                for="login-username">Username</label>
                            <input id="login-username" type="text" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"
                                placeholder="Enter your username" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"
                                for="login-password">Password</label>
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
                            <label class="block text-sm font-medium text-gray-700 mb-1"
                                for="signup-username">Username</label>
                            <input id="signup-username" type="text" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"
                                placeholder="Choose a username" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"
                                for="signup-password">Password</label>
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
                <button id="auth-toggle-btn" onclick="toggleAuthMode()"
                    class="font-medium text-black hover:underline ml-1">Sign up</button>
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" class="w-5 h-5">
                                <path d="M5 21v-6" />
                                <path d="M12 21V3" />
                                <path d="M19 21V9" />
                            </svg>
                        </div>
                        <span class="text-lg tracking-tight font-medium text-black">Ledgerline</span>
                    </div>

                    <div class="flex items-center gap-4">
                        <span id="user-greeting" class="text-sm text-gray-500 hidden md:inline"></span>
                        <button class="text-gray-400 hover:text-black transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" class="w-5 h-5">
                                <path d="M10.268 21a2 2 0 0 0 3.464 0" />
                                <path
                                    d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326" />
                            </svg>
                        </button>
                        <div onclick="handleLogout()"
                            class="h-8 w-8 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-grow">
            <?php if (!$hasConnectors): ?>
                <div id="empty-state-prompt" class="min-h-[calc(100vh-64px)] flex items-center justify-center bg-white p-6">
                    <div class="max-w-md w-full text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gray-50 mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                class="text-gray-400">
                                <path d="M12 20v-6M9 20V10M15 20V4M3 20h18" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-medium text-black mb-2">Build your portfolio</h2>
                        <p class="text-gray-500 mb-8">Connect a data source to start tracking your net worth and holdings in
                            real-time.</p>

                        <button onclick="toggleSourceModal()"
                            class="inline-flex items-center gap-2 bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-gray-800 transition-all shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14" />
                                <path d="M12 5v14" />
                            </svg>
                            Add Data Source
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">


                    <!-- Net Worth Summary -->
                    <div class="mb-10">
                        <h1 class="text-sm font-medium text-gray-500 mb-1">Total Net Worth</h1>
                        <div class="flex items-baseline gap-3">
                            <h2 id="net-worth" class="text-4xl tracking-tight font-medium text-black">--</h2>
                            <span id="net-worth-change"
                                class="inline-flex items-center gap-1 text-sm font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" class="w-3.5 h-3.5">
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
                                <div id="accounts-list" class="space-y-3">
                                    <div class="animate-pulse flex space-x-4 p-4">
                                        <div class="rounded-lg bg-gray-200 h-10 w-10"></div>
                                        <div class="flex-1 space-y-2 py-1">
                                            <div class="h-2 bg-gray-200 rounded w-3/4"></div>
                                            <div class="h-2 bg-gray-200 rounded w-1/2"></div>
                                        </div>
                                    </div>
                                </div>

                                <button onclick="toggleSourceModal()"
                                    class="w-full mt-3 bg-transparent border border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-gray-400 hover:bg-gray-50 transition-all flex flex-col items-center justify-center gap-2 text-gray-500 h-28">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" class="w-5 h-5">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    <span class="text-sm font-medium">Add data source</span>
                                </button>
                            </div>
                        </div>


                        <!-- Right Column: Holdings Table -->
                        <div class="lg:col-span-2">

                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base tracking-tight font-medium text-black">Your Holdings</h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600">
                                        Total Assets
                                    </span>
                                </div>
                            </div>

                            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-gray-100 bg-gray-50/50">
                                            <th class="text-left text-xs font-medium text-gray-500 px-4 py-3">Asset Symbol
                                            </th>
                                            <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">Quantity</th>
                                            <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">Current Price
                                            </th>
                                            <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">Total Value
                                            </th>
                                            <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">Source
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="holdings-table">
                                        <tr>
                                            <td colspan="5" class="text-center text-sm text-gray-400 px-4 py-8">
                                                <div class="flex flex-col items-center gap-2">
                                                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-black">
                                                    </div>
                                                    <span>Retrieving your portfolio holdings...</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Market Data -->
                            <div class="mt-8">
                                <h3 class="text-base tracking-tight font-medium text-black mb-4">Market Overview</h3>
                                <div id="market-grid" class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <div class="text-center text-sm text-gray-400 col-span-full py-8">Loading market data...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>




            <?php endif; ?>
    </div>


    </main>
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div
            class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-xs text-gray-400">2026 Ledgerline</p>
            <div class="flex gap-4">
                <a href="#" onclick="handleLogout()"
                    class="text-xs text-gray-400 hover:text-black transition-colors cursor-pointer">Sign Out</a>
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


        // ---- Add new Data Source ----            
        function toggleSourceModal() {
            const modal = document.getElementById('source-modal');
            modal.classList.toggle('hidden');
        }

        async function connectSource(provider) {
            toggleSourceModal();

            try {
                const res = await fetch("/api/sources/connect", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ provider: provider })
                });

                if (res.ok) {
                    await fetchData();
                    window.location.reload();
                } else {
                    const data = await res.json();
                    alert("Connection failed: " + data.error);
                }
            } catch (err) {
                console.error("Connection error:", err);
            }
        }
        // ---- Dashboard ----            
        // Remove specific rhPortfolio/yfPortfolio variables. Use these instead:
        let userHoldings = {};
        let userConnectors = [];

        // async function fetchData() {
        //     try {
        //         // Fetch 1: Portfolio Data (Holdings, Net Worth)
        //         const portfolioRes = await fetch("/api/user/portfolio");
        //         const portfolioData = await portfolioRes.json();
        //         console.log(portfolioData)
        //         // Fetch 2: Connector Data (The Left Column Cards)
        //         const connectorRes = await fetch("/api/user/connectors");
        //         const connectorData = await connectorRes.json();
        //         console.log(connectorData)

        //         if (portfolioRes.ok && portfolioData.holdings) {
        //             renderHoldings(portfolioData.holdings);
        //             updateNetWorth(portfolioData.holdings);
        //         }

        //         if (connectorRes.ok) {
        //             // Pass the results to your existing render function
        //             renderAccounts(connectorData);
        //         }
        //     } catch (err) {
        //         console.error("Dashboard Sync Error:", err);
        //     }
        // }

async function fetchData() {
    try {
        // Fetch both concurrently for efficiency
        const [portfolioRes, connectorRes] = await Promise.all([
            fetch("/api/user/portfolio"),
            fetch("/api/user/connectors")
        ]);

        const portfolioData = await portfolioRes.json();
        const connectorData = await connectorRes.json();

        // 1. Render Accounts (Left Column)
        if (connectorRes.ok) {
            renderAccounts(connectorData);
        }

        // 2. Render Holdings (Right Column)
        if (portfolioRes.ok && portfolioData.holdings) {
            renderHoldings(portfolioData.holdings);
            updateNetWorth(portfolioData.holdings);
        }
    } catch (err) {
        console.error("Dashboard Sync Error:", err);
    }
}
        function renderAccounts(connectors) {
            console.log("Raw Connectors Data:", connectors);

            const list = document.getElementById("accounts-list");
            if (!list) return;

            list.innerHTML = "";

            const accounts = Array.isArray(connectors) ? connectors : Object.values(connectors || {});

            if (accounts.length === 0) {
                list.innerHTML = `<p class="text-xs text-gray-400 italic p-4 text-center">No accounts found.</p>`;
                return;
            }

            accounts.forEach(conn => {
                // Try every possible naming convention for the provider
                const info = conn.authentication_information || conn.authenticationInformation || {};
                const provider = info.provider || conn.provider || "Unknown Source";
                const id = conn.id ? conn.id.slice(-6) : "......";

                const card = document.createElement("div");
                card.className = "bg-white border border-gray-200 rounded-xl p-4 shadow-sm mb-3";
                card.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center border border-gray-100 text-black">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-black capitalize">${provider}</h4>
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest">ID: ...${id}</p>
                </div>
            </div>
        `;
                list.appendChild(card);
            });
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


        // 1. Helper to group stocks by symbol
        function getAggregatedHoldings(rawHoldings) {
            const grouped = {};

            // Ensure rawHoldings is an object we can iterate
            if (!rawHoldings || typeof rawHoldings !== 'object') return {};

            Object.entries(rawHoldings).forEach(([ticker, data]) => {
                if (!data) return; // Skip if data is null

                const symbol = ticker;
                const qty = parseFloat(data.qty) || 0;
                // Check for 'price' or fallback to a default for now
                const price = parseFloat(data.price) || 150;

                if (!grouped[symbol]) {
                    grouped[symbol] = {
                        symbol: symbol,
                        qty: 0,
                        price: price,
                        sources: new Set()
                    };
                }

                grouped[symbol].qty += qty;

                // Handle the 'sources' array from your new PHP logic 
                // OR the single 'source' string from the old logic
                if (Array.isArray(data.sources)) {
                    data.sources.forEach(s => {
                        if (s.name) grouped[symbol].sources.add(s.name);
                    });
                } else if (data.source) {
                    grouped[symbol].sources.add(data.source);
                }
            });

            return grouped;
        }

        function renderHoldings(rawHoldings) {
            const tbody = document.getElementById("holdings-table");
            if (!tbody) return;
            tbody.innerHTML = "";

            const aggregated = getAggregatedHoldings(rawHoldings);
            const symbols = Object.keys(aggregated);

            if (symbols.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-12 text-gray-400">No holdings found.</td></tr>';
                return;
            }

            symbols.forEach(symbol => {
                const data = aggregated[symbol];
                const totalValue = data.qty * data.price;

                // 1. Create the row element first
                const row = document.createElement("tr");
                row.className = "border-b border-gray-50 hover:bg-gray-50/50 transition-colors";

                // 2. Generate the source badges
                const sourceTags = Array.from(data.sources).map(src => `
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest bg-gray-100 text-gray-400 border border-gray-200">
                ${src}
            </span>
        `).join("");

                // 3. Set the row content
                row.innerHTML = `
            <td class="px-4 py-4 text-sm font-medium text-black">${data.symbol}</td>
            <td class="px-4 py-4 text-sm text-right text-gray-600">${data.qty.toLocaleString()}</td>
            <td class="px-4 py-4 text-sm text-right text-gray-600">${fmt(data.price)}</td>
            <td class="px-4 py-4 text-sm text-right font-medium text-black">${fmt(totalValue)}</td>
            <td class="px-4 py-4 text-sm text-right">
                <div class="flex justify-end gap-1 flex-wrap">
                    ${sourceTags}
                </div>
            </td>
        `;

                // 4. Append the finished row to the table
                tbody.appendChild(row);
            });
        }
        function renderMarketOverview() {
            const grid = document.getElementById("market-grid");
            const indices = [
                { name: "S&P 500", val: "5,123.42", chg: "+0.45%" },
                { name: "NASDAQ", val: "16,274.94", chg: "+1.12%" },
                { name: "DJIA", val: "39,043.32", chg: "-0.12%" }
            ];
            grid.innerHTML = indices.map(i => `
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                <p class="text-[10px] uppercase tracking-wider text-gray-500 font-semibold">${i.name}</p>
                <p class="text-sm font-medium text-black">${i.val}</p>
                <p class="text-[10px] ${i.chg.startsWith('+') ? 'text-emerald-600' : 'text-red-600'}">${i.chg}</p>
            </div>
        `).join('');
        }

        function updateNetWorth(rawHoldings) {
            let total = 0;

            // rawHoldings is an object where keys are Tickers
            Object.values(rawHoldings || {}).forEach(item => {
                const q = parseFloat(item.qty) || 0;
                // Fallback to 150 (or any number) if item.price is missing
                const p = parseFloat(item.price) || 150;
                total += (q * p);
            });

            const netWorthEl = document.getElementById("net-worth");
            if (netWorthEl) {
                netWorthEl.textContent = fmt(total);
            }

            // Also update the "Last Updated" timestamp
            const lastUpdatedEl = document.getElementById("last-updated");
            if (lastUpdatedEl) {
                lastUpdatedEl.textContent = "Last updated: " + new Date().toLocaleTimeString();
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            const dashboard = document.getElementById("dashboard");
            if (dashboard && !dashboard.classList.contains("hidden")) {
                fetchData();
                renderMarketOverview();
            }
        });
    </script>
</body>

<?php
// Just the IDs and the display names
$predefined_connectors = [
    'robinhood' => 'Robinhood',
    'yahoo' => 'Yahoo Finance',
    'ibkr' => 'Interactive Brokers',
    'fidelity' => 'Fidelity',
    'vanguard' => 'Vanguard',
    'etrade' => 'ETrade'
];
?>

<div id="source-modal"
    class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-black">Connect Portfolio</h3>
            <button onclick="toggleSourceModal()" class="text-gray-400 hover:text-black">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
            </button>
        </div>
        <div class="space-y-2">
            <?php foreach ($predefined_connectors as $id => $name): ?>
                <button onclick="connectSource('<?php echo $id; ?>')"
                    class="w-full flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:bg-gray-50 hover:border-gray-300 transition-all text-left">
                    <span class="text-sm font-medium text-black"><?php echo $name; ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-gray-300">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</html>
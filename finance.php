<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon"
        href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTkMHmD22zgGgO1gQ5TCDm9MyRMRWFJ4fz4JQ&s">
    <title>Finance Tools | DriveSmart Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brandGold: '#0d6efd',
                        brandGoldHover: '#0a54c7',
                        brandLuxuryDark: '#14171c',
                        brandSlate: '#6b7178',
                        // legacy aliases kept so any residual template classes still resolve
                        'smart-blue': '#14171c',
                        'smart-teal': '#0d6efd',
                        'smart-light': '#f3f4f6',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Barlow Condensed', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="shared-styles.css">

    <style>
        .eyebrow-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #0d6efd;
            background: rgba(13, 110, 253, 0.08);
            border: 1px solid rgba(13, 110, 253, 0.25);
            padding: 0.2rem 0.65rem;
            border-radius: 999px;
        }

        /* Travel result card animation */
        #travelResults {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        #travelResults.hidden {
            opacity: 0;
            pointer-events: none;
        }
        #travelResults.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
    <script src="main.js?v=<?php echo filemtime(__DIR__ . '/main.js'); ?>"></script>
</head>

<body class="antialiased text-brandLuxuryDark bg-white">

    <!-- Navigation Bar -->
    <header class="sticky top-0 z-50 bg-brandLuxuryDark border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                <!-- Logo/Title -->
                <a href="index.html" class="order-1 flex items-center gap-2.5">
                    <span class="brand-mark"></span>
                    <span class="brand-text">DriveSmart</span>
                </a>

                <!-- Page Navigation -->
                <nav class="order-3 sm:order-2 flex flex-wrap w-full sm:w-auto items-center justify-center gap-1">
                    <a href="index.html" class="nav-link">Home</a>
                    <a href="recommendation.php" class="nav-link">Recommendations</a>
                    <a href="finance.php" class="nav-link active">Finance Tools</a>
                </nav>

                <!-- Timezone Widget -->
                <div data-timezone-widget class="order-2 sm:order-3"></div>
            </div>
        </div>
    </header>

    <main>
        <!-- 1. Hero Section -->
        <section class="bg-brandLuxuryDark text-white py-16 sm:py-20 lg:py-24 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[4px] bg-gradient-to-r from-brandGold via-blue-400 to-brandGold"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                <span class="eyebrow-tag mb-4">Premium Finance Suite</span>
                <h1 class="mt-3 text-5xl sm:text-6xl font-bold tracking-tight mb-3 leading-[1.05]">
                    Calculate Your <span class="text-brandGold">True Cost</span>
                </h1>
                <p class="mt-4 text-lg max-w-4xl mx-auto text-white/70 font-normal">
                    Buying a car involves much more than the sticker price. Understand your total budget, factoring in taxes, insurance, maintenance, and your monthly salary.
                </p>
            </div>
        </section>

        <!-- 2. Live Fuel Price Section -->
        <section class="py-14 bg-white border-b border-slate-200/60">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative bg-white rounded-3xl premium-shadow border border-slate-200/60 overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-[4px] bg-gradient-to-r from-brandGold via-blue-400 to-brandGold z-10"></div>
                    <div class="p-6 sm:p-8">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                            <h3 class="text-lg font-bold text-brandLuxuryDark flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-brandGold/10 ring-1 ring-brandGold/30">
                                    <i class="fa-solid fa-gas-pump text-brandGoldHover text-sm"></i>
                                </span>
                                This Week's Fuel Prices in Malaysia
                            </h3>
                            <span id="fuelPriceDate" class="text-xs font-semibold text-brandSlate bg-slate-50 border border-slate-200 rounded-full px-3 py-1.5">Loading&hellip;</span>
                        </div>

                        <div id="fuelPriceLoading" class="flex items-center gap-2 text-sm text-brandSlate py-6">
                            <span class="spinner"></span> Fetching latest prices&hellip;
                        </div>

                        <p id="fuelPriceError" class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-2xl p-3"></p>

                        <div id="fuelPriceGrid" class="hidden grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-center">
                                <p class="text-[10px] uppercase tracking-[0.15em] text-slate-400">RON95</p>
                                <p id="ron95Price" class="mt-2 text-2xl font-black text-brandLuxuryDark">RM 0.00</p>
                                <p id="ron95Change" class="mt-1 text-xs font-semibold"></p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-center">
                                <p class="text-[10px] uppercase tracking-[0.15em] text-slate-400">RON97</p>
                                <p id="ron97Price" class="mt-2 text-2xl font-black text-brandLuxuryDark">RM 0.00</p>
                                <p id="ron97Change" class="mt-1 text-xs font-semibold"></p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-center">
                                <p class="text-[10px] uppercase tracking-[0.15em] text-slate-400">Diesel</p>
                                <p id="dieselPrice" class="mt-2 text-2xl font-black text-brandLuxuryDark">RM 0.00</p>
                                <p id="dieselChange" class="mt-1 text-xs font-semibold"></p>
                            </div>
                        </div>

                        <p class="mt-5 text-xs text-slate-400">Source: <a href="https://data.gov.my" target="_blank" rel="noopener noreferrer" class="underline hover:text-brandGoldHover">data.gov.my</a> official weekly fuel price ceiling.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- 3. TRAVEL INFORMATION + AFFORDABILITY CALCULATOR                -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <section class="py-16 bg-slate-50 border-b border-slate-200/60" id="travel-section">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Section Header -->
                <div class="mb-10">
                    <h2 class="text-sm font-semibold text-brandGoldHover uppercase tracking-wider mb-1">Plan Ahead</h2>
                    <h3 class="text-3xl font-extrabold text-brandLuxuryDark">Commute &amp; Affordability Tools</h3>
                    <p class="mt-3 text-brandSlate font-light max-w-2xl">
                        Estimate your real fuel cost from your daily commute, and work out the maximum car price that comfortably fits your monthly salary.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

                    <!-- ══════════════ Travel Information Card ══════════════ -->
                    <div class="relative bg-white rounded-3xl premium-shadow border border-slate-200/60 overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-[4px] bg-gradient-to-r from-brandGold via-blue-400 to-brandGold z-10"></div>

                        <div class="p-6 sm:p-8 space-y-6">
                            <h4 class="text-lg font-bold text-brandLuxuryDark flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-brandGold/10 ring-1 ring-brandGold/30">
                                    <i class="fa-solid fa-location-dot text-brandGoldHover text-sm"></i>
                                </span>
                                Travel Information
                            </h4>

                            <form id="travelForm" novalidate class="space-y-5">

                                <!-- Home Address -->
                                <div class="space-y-2">
                                    <label for="home_address" class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">
                                        Home Address
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                            <i class="fa-solid fa-house text-sm"></i>
                                        </span>
                                        <input
                                            type="text"
                                            id="home_address"
                                            name="home_address"
                                            placeholder="e.g. Ampang, Kuala Lumpur"
                                            class="input-glow w-full pl-11 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm transition-all outline-none text-brandLuxuryDark font-medium placeholder-slate-400"
                                            required
                                        >
                                    </div>
                                    <p class="text-travelError-home text-red-500 text-xs font-semibold italic pl-1 hidden">⚠️ Please enter your home address.</p>
                                </div>

                                <!-- Workplace Address -->
                                <div class="space-y-2">
                                    <label for="workplace_address" class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">
                                        Workplace Address
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                            <i class="fa-solid fa-building text-sm"></i>
                                        </span>
                                        <input
                                            type="text"
                                            id="workplace_address"
                                            name="workplace_address"
                                            placeholder="e.g. KLCC, Kuala Lumpur"
                                            class="input-glow w-full pl-11 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm transition-all outline-none text-brandLuxuryDark font-medium placeholder-slate-400"
                                            required
                                        >
                                    </div>
                                    <p class="text-travelError-work text-red-500 text-xs font-semibold italic pl-1 hidden">⚠️ Please enter your workplace address.</p>
                                </div>

                                <!-- Working Days Per Month -->
                                <div class="space-y-2">
                                    <label for="working_days" class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">
                                        Working Days Per Month
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                            <i class="fa-solid fa-calendar-days text-sm"></i>
                                        </span>
                                        <input
                                            type="number"
                                            id="working_days"
                                            name="working_days"
                                            min="1"
                                            max="31"
                                            value="22"
                                            class="input-glow w-full pl-11 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm transition-all outline-none text-brandLuxuryDark font-medium"
                                            required
                                        >
                                    </div>
                                    <p class="text-xs text-brandSlate font-light pl-1">Typical full-time office: 22 days/month.</p>
                                    <p class="text-travelError-days text-red-500 text-xs font-semibold italic pl-1 hidden">⚠️ Please enter a value between 1 and 31.</p>
                                </div>

                                <!-- Submit -->
                                <button
                                    type="submit"
                                    id="travelSubmitBtn"
                                    class="w-full flex items-center justify-center gap-2 bg-brandLuxuryDark hover:bg-slate-800 text-white font-semibold py-4 rounded-2xl shadow-lg shadow-slate-900/10 transition-all transform active:scale-[0.98]"
                                >
                                    <i class="fa-solid fa-route text-xs text-brandGold"></i>
                                    <span id="travelBtnLabel">Calculate Commute Distance</span>
                                    <span id="travelSpinner" class="spinner hidden"></span>
                                </button>

                                <!-- API / Network Error Banner -->
                                <div id="travelApiError" class="hidden rounded-2xl bg-red-50 border border-red-200 p-3 text-sm text-red-700 font-medium flex items-start gap-2">
                                    <svg class="h-5 w-5 mt-0.5 flex-shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span id="travelApiErrorMsg">An error occurred.</span>
                                </div>
                            </form>

                            <!-- ── Results Panel ──────────────────────────────────────── -->
                            <div id="travelResultsWrapper">

                                <!-- Placeholder shown before first successful calculation -->
                                <div id="travelPlaceholder" class="flex flex-col items-center justify-center text-center py-12 text-slate-400">
                                    <i class="fa-solid fa-route text-4xl mb-4 opacity-30"></i>
                                    <p class="font-semibold text-brandLuxuryDark">Your commute results will appear here</p>
                                    <p class="text-sm mt-1 max-w-xs text-brandSlate font-light">Fill in the form above and click <strong>Calculate</strong> to see your monthly driving distance.</p>
                                </div>

                                <!-- Results (hidden until API responds) -->
                                <div id="travelResults" class="hidden space-y-4">

                                    <!-- Header -->
                                    <div class="flex items-center gap-3 mb-2 pt-2 border-t border-slate-100">
                                        <div class="h-10 w-10 rounded-full bg-brandGold flex items-center justify-center flex-shrink-0 mt-4">
                                            <i class="fa-solid fa-check text-brandLuxuryDark text-sm"></i>
                                        </div>
                                        <div class="mt-4">
                                            <p class="font-bold text-brandLuxuryDark text-lg leading-tight">Commute Summary</p>
                                            <p class="text-xs text-brandSlate">Based on driving route via OpenRouteService</p>
                                        </div>
                                    </div>

                                    <!-- Metric Cards -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                                        <!-- One-way distance -->
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-white flex items-center justify-center flex-shrink-0 ring-1 ring-slate-200">
                                                <i class="fa-solid fa-arrow-right-long text-brandLuxuryDark text-sm"></i>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">One-Way Distance</p>
                                                <p id="result_oneway" class="text-xl font-extrabold text-brandLuxuryDark mt-0.5">— km</p>
                                            </div>
                                        </div>

                                        <!-- Travel time -->
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-white flex items-center justify-center flex-shrink-0 ring-1 ring-slate-200">
                                                <i class="fa-regular fa-clock text-brandGoldHover text-sm"></i>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Est. Travel Time</p>
                                                <p id="result_time" class="text-xl font-extrabold text-brandGoldHover mt-0.5">— min</p>
                                            </div>
                                        </div>

                                        <!-- Round-trip distance -->
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-white flex items-center justify-center flex-shrink-0 ring-1 ring-slate-200">
                                                <i class="fa-solid fa-right-left text-purple-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Daily Round Trip</p>
                                                <p id="result_roundtrip" class="text-xl font-extrabold text-purple-600 mt-0.5">— km</p>
                                            </div>
                                        </div>

                                        <!-- Monthly distance — featured card -->
                                        <div class="rounded-2xl bg-brandLuxuryDark p-4 flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
                                                <i class="fa-solid fa-gauge-high text-brandGold text-sm"></i>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-semibold text-slate-300 uppercase tracking-wider">Monthly Distance</p>
                                                <p id="result_monthly" class="text-xl font-extrabold text-white mt-0.5">— km</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fuel Cost Tip -->
                                    <div id="fuelTipBox" class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-sm text-blue-800">
                                        <p class="font-bold mb-1 flex items-center gap-1.5">
                                            <i class="fa-solid fa-gas-pump text-blue-500 text-xs"></i>
                                            Estimated Monthly Fuel Cost
                                        </p>
                                        <p id="fuelTipText" class="text-blue-700">—</p>
                                        <p id="fuelPriceBasis" class="text-blue-600 text-xs mt-1">*Based on the latest RON95 price and average fuel consumption of 10L/100km. Actual costs vary by vehicle and driving style.</p>
                                    </div>

                                    <!-- Find Nearby EV Charging Stations -->
                                    <button type="button" id="findEvBtn"
                                        class="w-full flex items-center justify-center gap-2 bg-white border border-emerald-200 text-emerald-700 font-semibold py-3.5 rounded-2xl hover:bg-emerald-50 transition-all">
                                        <i class="fa-solid fa-charging-station text-sm"></i>
                                        Find Nearby EV Charging Stations
                                    </button>

                                    <!-- Disclaimer -->
                                    <p class="text-xs text-slate-400 leading-relaxed">
                                        Route data provided by <a href="https://openrouteservice.org/" target="_blank" rel="noopener noreferrer" class="underline hover:text-brandGoldHover">OpenRouteService</a>. Distance and time are estimates based on standard driving conditions and may differ from actual travel.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══════════════ Car Affordability Calculator Card ══════════════ -->
                    <div class="relative bg-white rounded-3xl premium-shadow border border-slate-200/60 overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-[4px] bg-gradient-to-r from-brandGold via-blue-400 to-brandGold z-10"></div>

                        <div class="p-6 sm:p-8 space-y-6">
                            <div>
                                <h4 class="text-lg font-bold text-brandLuxuryDark flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-brandGold/10 ring-1 ring-brandGold/30">
                                        <i class="fa-solid fa-calculator text-brandGoldHover text-sm"></i>
                                    </span>
                                    Car Affordability Calculator
                                </h4>
                                <p class="mt-2 text-sm text-brandSlate font-light pl-[3.25rem]">
                                    Find the maximum car price that fits comfortably within your monthly salary.
                                </p>
                            </div>

                            <div class="space-y-5">

                                <!-- Net Salary -->
                                <div class="space-y-2">
                                    <label for="netSalary" class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">
                                        Net Monthly Salary (RM)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                            <i class="fa-solid fa-sack-dollar text-sm"></i>
                                        </span>
                                        <input type="number" id="netSalary" placeholder="e.g. 4500" min="0"
                                            class="input-glow w-full pl-11 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm transition-all outline-none text-brandLuxuryDark font-medium placeholder-slate-400">
                                    </div>
                                </div>

                                <!-- Deposit -->
                                <div class="space-y-2">
                                    <label for="deposit" class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">
                                        Down Payment / Deposit (RM)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                            <i class="fa-solid fa-wallet text-sm"></i>
                                        </span>
                                        <input type="number" id="deposit" placeholder="e.g. 10000" min="0" value="0"
                                            class="input-glow w-full pl-11 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm transition-all outline-none text-brandLuxuryDark font-medium placeholder-slate-400">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Loan Tenure -->
                                    <div class="space-y-2">
                                        <label for="loanYears" class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">
                                            Loan Tenure
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                                <i class="fa-regular fa-calendar text-sm"></i>
                                            </span>
                                            <select id="loanYears"
                                                class="input-glow w-full pl-11 pr-8 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm transition-all outline-none text-brandLuxuryDark font-medium appearance-none cursor-pointer">
                                                <option value="2">2 years</option>
                                                <option value="3">3 years</option>
                                                <option value="5" selected>5 years</option>
                                                <option value="7">7 years</option>
                                                <option value="9">9 years</option>
                                            </select>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                                <i class="fa-solid fa-chevron-down text-xs"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Interest Rate -->
                                    <div class="space-y-2">
                                        <label for="interestRate" class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">
                                            Interest (% p.a.)
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                                <i class="fa-solid fa-percent text-sm"></i>
                                            </span>
                                            <input type="number" id="interestRate" step="0.1" min="0" value="3.5"
                                                class="input-glow w-full pl-11 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm transition-all outline-none text-brandLuxuryDark font-medium">
                                        </div>
                                    </div>
                                </div>

                                <button id="calculateButton" type="button"
                                    class="w-full flex items-center justify-center gap-2 bg-brandLuxuryDark hover:bg-slate-800 text-white font-semibold py-4 rounded-2xl shadow-lg shadow-slate-900/10 transition-all transform active:scale-[0.98]">
                                    <i class="fa-solid fa-calculator text-xs text-brandGold"></i>
                                    <span>Calculate Max Car Price</span>
                                </button>

                                <span id="displayMaxPrice" class="sr-only">RM 0</span>
                            </div>

                            <!-- Results -->
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5 space-y-3">
                                <div class="rounded-xl border border-slate-200 bg-white p-4 text-center">
                                    <p class="text-[10px] uppercase tracking-[0.15em] text-slate-400">Maximum Suitable Car Price</p>
                                    <p id="suitableCarPrice" class="mt-2 text-3xl sm:text-4xl font-black text-brandGoldHover">RM 0</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="rounded-xl border border-slate-200 bg-white p-3.5">
                                        <p class="text-[9px] uppercase tracking-[0.15em] text-slate-400">Max Monthly Payment</p>
                                        <p id="monthlyPaymentResult" class="mt-1.5 text-lg font-bold text-brandLuxuryDark">RM 0</p>
                                    </div>
                                    <div class="rounded-xl border border-slate-200 bg-white p-3.5">
                                        <p class="text-[9px] uppercase tracking-[0.15em] text-slate-400">Loan Amount Financed</p>
                                        <p id="maxLoanAmountDisplay" class="mt-1.5 text-sm font-bold text-brandLuxuryDark">Max Loan Amount Financed: RM 0</p>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-slate-400 leading-relaxed">
                                *Estimate assumes a maximum monthly repayment of 18% of your net salary, a common bank affordability guideline. Actual loan approval depends on your full DSR, credit history, and the bank's own policy.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- END: TRAVEL INFORMATION + CALCULATOR SECTION                    -->
        <!-- ═══════════════════════════════════════════════════════════════ -->

        <!-- 4. True Cost of Ownership (TCO) Components -->
        <section class="py-16 sm:py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-sm font-semibold text-brandGoldHover uppercase tracking-wider mb-1">Look Beyond The Sticker Price</h2>
                    <h3 class="text-3xl sm:text-4xl font-extrabold text-brandLuxuryDark">
                        The Three Hidden Costs of Car Ownership
                    </h3>
                    <p class="mt-4 text-lg text-brandSlate font-light max-w-3xl mx-auto">
                        Ignoring these crucial costs is the fastest way to break your budget.
                    </p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white p-8 rounded-3xl premium-shadow border border-slate-200/60 border-t-4 border-t-brandGold">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brandGold/10 ring-1 ring-brandGold/30 mb-4">
                            <i class="fa-solid fa-file-invoice-dollar text-brandGoldHover"></i>
                        </div>
                        <h3 class="text-xl font-bold text-brandLuxuryDark mb-3">Taxes &amp; Registration</h3>
                        <p class="text-brandSlate mb-4 font-light">These are one-time or recurring government fees that drastically increase the immediate cost.</p>
                        <ul class="list-disc list-inside space-y-2 text-slate-600 pl-2 text-sm">
                            <li>Sales Tax: Varies significantly by state/region (often 5% to 10% of the sale price).</li>
                            <li>Title &amp; Registration Fees: Mandatory fees to legally own and operate the vehicle.</li>
                            <li>Dealership Fees: Includes documentation fees or preparation fees (often negotiable, but always check).</li>
                        </ul>
                    </div>
                    <div class="bg-white p-8 rounded-3xl premium-shadow border border-slate-200/60 border-t-4 border-t-brandGold">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brandGold/10 ring-1 ring-brandGold/30 mb-4">
                            <i class="fa-solid fa-shield-halved text-brandGoldHover"></i>
                        </div>
                        <h3 class="text-xl font-bold text-brandLuxuryDark mb-3">Insurance Premiums</h3>
                        <p class="text-brandSlate mb-4 font-light">A major monthly expense that depends on the car, your driving history, and where you live.</p>
                        <ul class="list-disc list-inside space-y-2 text-slate-600 pl-2 text-sm">
                            <li>Car Type: High-performance, luxury, or frequently stolen cars cost more to insure.</li>
                            <li>Deductibles: Choosing higher deductibles lowers your monthly premium but increases your out-of-pocket risk.</li>
                            <li>Coverage: If you finance or lease, comprehensive and collision coverage are mandatory.</li>
                        </ul>
                    </div>
                    <div class="bg-white p-8 rounded-3xl premium-shadow border border-slate-200/60 border-t-4 border-t-brandGold">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brandGold/10 ring-1 ring-brandGold/30 mb-4">
                            <i class="fa-solid fa-gas-pump text-brandGoldHover"></i>
                        </div>
                        <h3 class="text-xl font-bold text-brandLuxuryDark mb-3">Maintenance &amp; Fuel</h3>
                        <p class="text-brandSlate mb-4 font-light">The ongoing, operational costs that define the long-term affordability of your vehicle.</p>
                        <ul class="list-disc list-inside space-y-2 text-slate-600 pl-2 text-sm">
                            <li>Fuel/Energy: Directly linked to the vehicle's MPG and your commute distance.</li>
                            <li>Routine Maintenance: Oil changes, tire rotations, and scheduled service fees.</li>
                            <li>Depreciation: The single biggest loss — cars lose 30–40% of their value in the first five years.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. Final Financial Advice CTA -->
        <section class="py-16 sm:py-20 bg-slate-50">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative rounded-3xl premium-shadow border border-slate-200/60 overflow-hidden bg-brandLuxuryDark px-6 sm:px-12 py-12 sm:py-16 text-center">
                    <div class="absolute top-0 left-0 right-0 h-[4px] bg-gradient-to-r from-brandGold via-blue-400 to-brandGold"></div>
                    <span class="eyebrow-tag">Final Word</span>
                    <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-white">
                        Don't Buy More Than You Need
                    </h2>
                    <p class="mt-4 text-lg text-slate-300 max-w-3xl mx-auto font-light">
                        A car is a tool, not an investment. Prioritize reliability and low TCO (True Cost of Ownership) over luxury features if you are on a strict budget.
                    </p>
                    <div class="mt-8">
                        <a href="Recommendation.php" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-sm sm:text-base font-bold rounded-full shadow-lg text-brandLuxuryDark bg-brandGold hover:bg-brandGoldHover transition duration-300 transform hover:-translate-y-0.5">
                            Run Your Personalized Car Recommendation
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-brandLuxuryDark py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="font-display text-lg font-semibold text-white mb-2">Drive<span class="text-brandGold">Smart</span></p>
                <div class="space-x-4 text-sm mb-3 text-white/60">
                    <a href="#" class="hover:text-brandGold transition duration-150">Privacy Policy</a>
                    <span class="text-white/20">|</span>
                    <a href="#" class="hover:text-brandGold transition duration-150">Terms of Service</a>
                    <span class="text-white/20">|</span>
                    <a href="#" class="hover:text-brandGold transition duration-150">Contact</a>
                </div>
                <p class="text-xs text-white/40">&copy; 2026 DriveSmart. Premium Auto Finance Assessment. All rights reserved.</p>
            </div>
        </footer>
    </main>

    <!-- ══════════════ EV Charging Stations Modal (popup) ══════════════ -->
    <div id="evChargersModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div id="evChargersModalBackdrop" class="absolute inset-0 bg-brandLuxuryDark/60 backdrop-blur-sm"></div>

        <div class="relative bg-white rounded-3xl premium-shadow w-full max-w-lg max-h-[85vh] overflow-y-auto p-6">
            <button type="button" id="evChargersModalClose"
                class="absolute top-4 right-4 h-9 w-9 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-brandSlate transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="flex items-center gap-2 mb-4 pr-10">
                <div class="h-9 w-9 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-charging-station text-emerald-600 text-sm"></i>
                </div>
                <div>
                    <p class="font-bold text-brandLuxuryDark text-sm leading-tight">Nearby EV Charging Stations</p>
                    <p class="text-xs text-brandSlate">Powered by Open Charge Map</p>
                </div>
            </div>

            <div id="evChargersLoading" class="hidden flex items-center gap-2 text-sm text-brandSlate py-4">
                <span class="spinner"></span> Looking up nearby charging stations…
            </div>

            <p id="evChargersMessage" class="hidden text-xs text-blue-700 bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3"></p>
            <p id="evChargersError" class="hidden text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg p-3 mb-3"></p>
            <p id="evChargersEmpty" class="hidden text-sm text-slate-400 py-2"></p>

            <ol id="evChargersList" class="space-y-3"></ol>
        </div>
    </div>

    <script>
    /* ─── Travel Information Form ─────────────────────────────────────────── */
    const travelForm       = document.getElementById('travelForm');
    const travelSubmitBtn  = document.getElementById('travelSubmitBtn');
    const travelBtnLabel   = document.getElementById('travelBtnLabel');
    const travelSpinner    = document.getElementById('travelSpinner');
    const travelResults    = document.getElementById('travelResults');
    const travelPlaceholder= document.getElementById('travelPlaceholder');
    const travelApiError   = document.getElementById('travelApiError');
    const travelApiErrorMsg= document.getElementById('travelApiErrorMsg');

    let lastHomeLat = null;
    let lastHomeLng = null;

    function setTravelLoading(loading) {
        travelSubmitBtn.disabled = loading;
        travelBtnLabel.textContent = loading ? 'Calculating…' : 'Calculate Commute Distance';
        travelSpinner.classList.toggle('hidden', !loading);
    }

    function showTravelError(msg) {
        travelApiError.classList.remove('hidden');
        travelApiErrorMsg.textContent = msg;
    }

    function hideTravelError() {
        travelApiError.classList.add('hidden');
        travelApiErrorMsg.textContent = '';
    }

    function formatTime(mins) {
        if (mins < 60) return mins + ' min';
        const h = Math.floor(mins / 60);
        const m = mins % 60;
        return m > 0 ? `${h}h ${m}min` : `${h}h`;
    }

    travelForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        hideTravelError();

        // Client-side validation
        const homeVal = document.getElementById('home_address').value.trim();
        const workVal = document.getElementById('workplace_address').value.trim();
        const daysVal = parseInt(document.getElementById('working_days').value, 10);

        let hasError = false;

        const homeErr = document.querySelector('.text-travelError-home');
        const workErr = document.querySelector('.text-travelError-work');
        const daysErr = document.querySelector('.text-travelError-days');

        if (!homeVal) { homeErr.classList.remove('hidden'); hasError = true; } else { homeErr.classList.add('hidden'); }
        if (!workVal) { workErr.classList.remove('hidden'); hasError = true; } else { workErr.classList.add('hidden'); }
        if (!daysVal || daysVal < 1 || daysVal > 31) { daysErr.classList.remove('hidden'); hasError = true; } else { daysErr.classList.add('hidden'); }

        if (hasError) return;

        setTravelLoading(true);

        try {
            const formData = new FormData();
            formData.append('home_address',      homeVal);
            formData.append('workplace_address', workVal);
            formData.append('working_days',      daysVal);

            const response = await fetch('travel_info.php', {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                throw new Error(`Server returned HTTP ${response.status}. Please try again.`);
            }

            const data = await response.json();

            if (!data.success) {
                showTravelError(data.error || 'An unexpected error occurred.');
                return;
            }

            // Populate results
            document.getElementById('result_oneway').textContent    = data.one_way_km + ' km';
            document.getElementById('result_roundtrip').textContent = data.round_trip_km + ' km';
            document.getElementById('result_monthly').textContent   = data.monthly_km + ' km';
            document.getElementById('result_time').textContent      = formatTime(data.travel_time_minutes);

            // Fuel cost estimate using the live RON95 price (falls back to RM2.05/L
            // if the fuel price API is unavailable), assuming 10L/100km consumption.
            const fuelData    = await window.DriveSmartFuelPrice;
            const ron95Price  = (fuelData && fuelData.success && fuelData.prices) ? fuelData.prices.ron95 : 2.05;
            const fuelCostPerKm  = (ron95Price * 10) / 100;
            const estimatedFuel  = (data.monthly_km * fuelCostPerKm).toFixed(2);
            document.getElementById('fuelTipText').textContent =
                `Driving ${data.monthly_km} km/month ≈ RM ${estimatedFuel} in fuel (petrol car estimate).`;
            document.getElementById('fuelPriceBasis').textContent =
                `*Based on RON95 at RM${ron95Price.toFixed(2)}/L and average fuel consumption of 10L/100km. Actual costs vary by vehicle and driving style.`;

            // Show results, hide placeholder
            travelPlaceholder.classList.add('hidden');
            travelResults.classList.remove('hidden');

            // Scroll results into view on mobile
            if (window.innerWidth < 1024) {
                travelResults.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            // Remember home coordinates (geocoded by travel_info.php via Nominatim)
            // so the "Find Nearby EV Charging Stations" button can look them up on demand.
            lastHomeLat = data.home_lat || null;
            lastHomeLng = data.home_lng || null;

        } catch (err) {
            showTravelError(err.message || 'Network error. Please check your connection and try again.');
        } finally {
            setTravelLoading(false);
        }
    });

    /* ─── EV Charging Station Lookup (Open Charge Map) ────────────────────── */
    const findEvBtn            = document.getElementById('findEvBtn');
    const evChargersModal       = document.getElementById('evChargersModal');
    const evChargersModalBackdrop = document.getElementById('evChargersModalBackdrop');
    const evChargersModalClose  = document.getElementById('evChargersModalClose');
    const evChargersLoading = document.getElementById('evChargersLoading');
    const evChargersMessage = document.getElementById('evChargersMessage');
    const evChargersError   = document.getElementById('evChargersError');
    const evChargersEmpty   = document.getElementById('evChargersEmpty');
    const evChargersList    = document.getElementById('evChargersList');

    function openEvModal() {
        evChargersModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEvModal() {
        evChargersModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    findEvBtn.addEventListener('click', function () {
        if (!lastHomeLat || !lastHomeLng) return;
        openEvModal();
        fetchEvChargers(lastHomeLat, lastHomeLng);
    });

    evChargersModalClose.addEventListener('click', closeEvModal);
    evChargersModalBackdrop.addEventListener('click', closeEvModal);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !evChargersModal.classList.contains('hidden')) closeEvModal();
    });

    function resetEvChargersUI() {
        evChargersMessage.classList.add('hidden');
        evChargersError.classList.add('hidden');
        evChargersEmpty.classList.add('hidden');
        evChargersList.innerHTML = '';
    }

    function renderEvChargerItem(station, index) {
        const li = document.createElement('li');
        li.className = 'bg-emerald-50/60 border border-emerald-100 rounded-lg p-3';

        const details = [];
        if (station.distance_km !== null && station.distance_km !== undefined) {
            details.push(`Distance: ${station.distance_km} km`);
        }
        details.push(`Address: ${station.address || 'Not available'}`);
        if (station.operator)     details.push(`Operator: ${station.operator}`);
        if (station.charger_type) details.push(`Charger Type: ${station.charger_type}`);
        if (station.num_points)   details.push(`Charging Points: ${station.num_points}`);

        li.innerHTML = `
            <p class="font-semibold text-brandLuxuryDark text-sm">${index + 1}. ${station.name}</p>
            <ul class="text-xs text-gray-600 mt-1 space-y-0.5 list-disc list-inside">
                ${details.map(d => `<li>${d}</li>`).join('')}
            </ul>
        `;
        return li;
    }

    async function fetchEvChargers(lat, lng) {
        resetEvChargersUI();
        evChargersLoading.classList.remove('hidden');

        try {
            const response = await fetch(`get_ev_chargers.php?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`);

            if (!response.ok) {
                throw new Error(`Server returned HTTP ${response.status}.`);
            }

            const data = await response.json();

            if (!data.success) {
                evChargersError.textContent = data.error || 'Could not load nearby EV charging stations right now.';
                evChargersError.classList.remove('hidden');
                return;
            }

            if (data.message) {
                evChargersMessage.textContent = data.message;
                evChargersMessage.classList.remove('hidden');
            }

            if (!data.stations || data.stations.length === 0) {
                evChargersEmpty.textContent = data.message
                    ? ''
                    : 'No EV charging stations were found near your home.';
                if (!data.message) {
                    evChargersEmpty.classList.remove('hidden');
                }
                return;
            }

            data.stations.forEach((station, index) => {
                evChargersList.appendChild(renderEvChargerItem(station, index));
            });

        } catch (err) {
            // The Finance page keeps working even if Open Charge Map is unavailable.
            evChargersError.textContent = err.message || 'Network error while loading EV charging stations.';
            evChargersError.classList.remove('hidden');
        } finally {
            evChargersLoading.classList.add('hidden');
        }
    }
    </script>
    <script src="timezone-widget.js"></script>
    <script src="fuel-price-widget.js"></script>
</body>
</html>
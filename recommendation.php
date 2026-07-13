<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon"
        href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTkMHmD22zgGgO1gQ5TCDm9MyRMRWFJ4fz4JQ&s">
    <title>Car Recommendations | DriveSmart Premium</title>
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
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .accordion-open {
            max-height: 500px;
        }

        .dimmed {
            background-color: #f1f5f9;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .step-hidden {
            display: none;
        }

        /* ---- Journey Progress Stepper ---- */
        .journey-track {
            position: relative;
            height: 4px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            overflow: visible;
        }

        .journey-fill {
            position: absolute;
            inset: 0 auto 0 0;
            height: 100%;
            width: 0%;
            border-radius: 999px;
            background: linear-gradient(90deg, #0d6efd, #0a54c7);
            transition: width 0.55s cubic-bezier(.65, 0, .35, 1);
        }

        .journey-car {
            position: absolute;
            top: 50%;
            left: 0%;
            transform: translate(-50%, -50%);
            transition: left 0.55s cubic-bezier(.65, 0, .35, 1);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.35));
            font-size: 18px;
            line-height: 1;
        }

        .step-dot {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.85rem;
            height: 1.85rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 800;
            transition: all 0.35s ease;
        }

        .step-dot.pending {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.55);
        }

        .step-dot.active {
            background: #0d6efd;
            color: #111315;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25);
            transform: scale(1.1);
        }

        .step-dot.done {
            background: rgba(255, 255, 255, 0.92);
            color: #111315;
        }

        .step-label {
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.4);
            transition: color 0.35s ease;
            white-space: nowrap;
        }

        .step-label.active {
            color: #0d6efd;
        }

        .eyebrow-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #1d4ed8;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 0.2rem 0.65rem;
            border-radius: 999px;
        }

        /* DSR yes/no + NCD toggle pair buttons */
        .dsr-btn {
            border: 1px solid transparent;
        }

        input[type="range"].gold-slider {
            accent-color: #0d6efd;
        }
    </style>
    <script src="main.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
                    <a href="recommendation.php" class="nav-link active">Recommendations</a>
                    <a href="finance.php" class="nav-link">Finance Tools</a>
                </nav>

                <!-- Timezone Widget -->
                <div data-timezone-widget class="order-2 sm:order-3"></div>
            </div>
        </div>
    </header>

    <main>
        <div class="max-w-2xl mx-auto bg-white premium-shadow rounded-3xl overflow-hidden border border-slate-200/60 mt-8 mb-14 relative">

            <div class="absolute top-0 left-0 right-0 h-[4px] bg-gradient-to-r from-brandGold via-blue-400 to-brandGold z-10"></div>

            <div class="bg-brandLuxuryDark px-6 sm:px-8 pt-8 pb-8 text-white">

                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-brandGold/30">
                        <i class="fa-solid fa-car-side text-brandGold text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-brandGold block mb-0.5">Priority Verification</span>
                        <h2 class="text-lg sm:text-xl font-bold tracking-tight">DriveSmart Eligibility Assessment</h2>
                    </div>
                </div>

                <!-- Journey progress: a car travels the route as each step is completed -->
                <div class="mt-7">
                    <div class="journey-track" id="journeyTrack">
                        <div class="journey-fill" id="journeyFill"></div>
                        <div class="journey-car" id="journeyCar">🚗</div>
                    </div>
                    <div class="mt-3 grid grid-cols-5 gap-1">
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="step-dot active" id="stepDot1">1</div>
                            <span class="step-label active" id="stepLabel1">Profile</span>
                        </div>
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="step-dot pending" id="stepDot2">2</div>
                            <span class="step-label" id="stepLabel2">Finance</span>
                        </div>
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="step-dot pending" id="stepDot3">3</div>
                            <span class="step-label" id="stepLabel3">DSR</span>
                        </div>
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="step-dot pending" id="stepDot4">4</div>
                            <span class="step-label" id="stepLabel4">CCRIS</span>
                        </div>
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="step-dot pending" id="stepDot5">5</div>
                            <span class="step-label" id="stepLabel5">Ownership</span>
                        </div>
                    </div>
                </div>

                <p id="stepIndicator" class="mt-4 text-center text-xs sm:text-sm font-semibold text-white bg-white/10 rounded-full py-1.5 px-3 inline-block mx-auto w-full">
                    Step 1 of 5 &middot; Personal &amp; Employment Details
                </p>
            </div>

            <form id="scoringForm" class="p-8 space-y-6">

                <div id="step1">
                    <div class="space-y-6">
                        <div class="border-b border-slate-100 pb-4">
                            <span class="eyebrow-tag">Step 1 of 5</span>
                            <h2 class="mt-2 text-lg font-bold text-brandLuxuryDark">Personal &amp; Employment Details</h2>
                            <p class="text-sm text-brandSlate font-light">Tell us a little about yourself so we can tailor your assessment.</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">Full Name</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <i class="fa-regular fa-user text-sm"></i>
                                </span>
                                <input type="text" id="nama" required
                                    class="input-glow w-full pl-11 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm transition-all outline-none text-brandLuxuryDark font-medium placeholder-slate-400">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">IC Number</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                        <i class="fa-regular fa-address-card text-sm"></i>
                                    </span>
                                    <input type="text" id="ic" placeholder="000000-00-0000" maxlength="14" required
                                        class="input-glow w-full pl-11 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm transition-all outline-none text-brandLuxuryDark font-medium placeholder-slate-400">
                                </div>
                                <p id="icError" class="hidden text-red-600 text-xs font-semibold italic pl-1">⚠️ Nombor IC
                                    tidak lengkap.</p>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">Age</label>
                                <input type="text" id="age" readonly
                                    class="w-full p-3.5 border border-slate-200 rounded-2xl bg-slate-100 text-brandLuxuryDark font-bold">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">Job Sector</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <i class="fa-solid fa-briefcase text-sm"></i>
                                </span>
                                <select id="pekerjaan" required
                                    class="input-glow w-full pl-11 pr-10 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm transition-all outline-none text-brandLuxuryDark font-medium appearance-none cursor-pointer">
                                    <option value="">Please Select</option>
                                    <option value="gov">Government / GLC</option>
                                    <option value="private">Private</option>
                                    <option value="self">Free Lancer / Self Employed</option>
                                    <option value="pesara">Pesara</option>
                                </select>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </span>
                            </div>
                            <p id="pesaraWarning" class="hidden text-red-600 text-xs font-semibold italic pl-1">⚠️ Pesara
                                must be below 70 years old.</p>
                        </div>

                        <div id="privateAccordion" class="accordion-content bg-slate-50 rounded-2xl">
                            <div class="p-4 space-y-4 border-l-4 border-brandGold">
                                <label class="block text-sm font-medium text-brandLuxuryDark mb-1">Job Industry</label>
                                <select id="industry" class="w-full p-2.5 border border-slate-200 rounded-xl bg-white text-sm">
                                    <option value="">Choose Industry...</option>
                                    <option>IT</option>
                                    <option>Banking & Finance</option>
                                    <option>Education</option>
                                    <option>Healthcare</option>
                                    <option>Manufacturing</option>
                                    <option>Construction</option>
                                    <option>Retail & Sales</option>
                                    <option>Logistics & Transportation</option>
                                    <option>Oil & Gas</option>
                                    <option>Telecommunications</option>
                                    <option>Hospitality & Tourism</option>
                                    <option>Food & Beverage</option>
                                    <option>Property/Real Estate</option>
                                    <option>Professional Services</option>
                                    <option>Business Owner</option>
                                    <option>Insurance</option>
                                    <option>Engineering</option>
                                    <option>E-commerce</option>
                                    <option>Others</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">Job Status</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <i class="fa-solid fa-circle-check text-sm"></i>
                                </span>
                                <select id="jobType" required
                                    class="input-glow w-full pl-11 pr-10 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm transition-all outline-none text-brandLuxuryDark font-medium appearance-none cursor-pointer">
                                    <option value="">Sila Pilih</option>
                                    <option value="permanent">Permanent</option>
                                    <option value="contract">Contract</option>
                                </select>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </span>
                            </div>
                        </div>

                        <button id="step1NextBtn" type="button" onclick="goToStep2()"
                            class="w-full flex items-center justify-center gap-2 bg-brandLuxuryDark hover:bg-slate-800 text-white font-semibold py-4 rounded-2xl shadow-lg shadow-slate-900/10 transition-all transform active:scale-[0.98]">
                            <span>Next: Financial Details</span>
                            <i class="fa-solid fa-arrow-right text-xs text-brandGold"></i>
                        </button>
                    </div>
                </div>

                <div id="step2" class="step-hidden">
                    <div class="space-y-6">
                        <div class="border-b border-slate-100 pb-4">
                            <span class="eyebrow-tag">Step 2 of 5</span>
                            <h2 class="mt-2 text-lg font-bold text-brandLuxuryDark">Financial Details</h2>
                            <p class="text-sm text-brandSlate font-light">These numbers help us estimate what you can comfortably afford.</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">Net Salary (RM)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <i class="fa-solid fa-sack-dollar text-sm"></i>
                                </span>
                                <input type="number" id="gaji" placeholder="Contoh: 3500" required
                                    class="input-glow w-full pl-11 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm transition-all outline-none text-brandLuxuryDark font-medium placeholder-slate-400">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">Loan Term (Years)</label>
                                <select id="loanYears" required
                                    class="input-glow w-full p-3.5 border border-slate-200 rounded-2xl bg-slate-50/50 outline-none text-sm text-brandLuxuryDark font-medium appearance-none cursor-pointer">
                                    <option value="">Select</option>
                                    <option value="3">3 Years</option>
                                    <option value="4">4 Years</option>
                                    <option value="5">5 Years</option>
                                    <option value="6">6 Years</option>
                                    <option value="7">7 Years</option>
                                    <option value="8">8 Years</option>
                                    <option value="9">9 Years</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">Deposit (RM)</label>
                                <input type="number" id="deposit" placeholder="Contoh: 25000" required
                                    class="input-glow w-full p-3.5 border border-slate-200 rounded-2xl bg-slate-50/50 outline-none text-sm text-brandLuxuryDark font-medium placeholder-slate-400">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">Interest Rate (%)</label>
                                <span id="interestValue" class="text-xs font-bold text-brandGoldHover">2.5%</span>
                            </div>
                            <input type="range" id="interestRate" min="1.0" max="7.0" step="0.1" value="2.5"
                                class="gold-slider w-full h-2 bg-slate-200 rounded-lg">
                            <div class="mt-3 rounded-2xl border border-blue-100 bg-blue-50/60 p-3.5 text-sm text-brandLuxuryDark">
                                <p class="font-semibold">Interest rates depend on your credit score, payment history, and lending decisions, such as banks.</p>
                                <p class="mt-1 text-brandSlate font-light">This is only an estimate of the rate you might receive.</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">CCRIS / CTOS Score</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <i class="fa-solid fa-gauge-high text-sm"></i>
                                </span>
                                <select id="creditScore" required
                                    class="input-glow w-full pl-11 pr-10 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm transition-all outline-none text-brandLuxuryDark font-medium appearance-none cursor-pointer">
                                    <option value="">Please Select CTOS Score</option>
                                    <option value="poor">300-649</option>
                                    <option value="fair">650-696</option>
                                    <option value="good">697-749</option>
                                    <option value="excellent">750-850+</option>
                                </select>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </span>
                            </div>
                            <p id="scoreLabel" class="text-xs font-bold pl-1"></p>
                            <p class="text-xs text-brandSlate font-light pl-1">
                                You can check your CTOS score at
                                <a href="https://ctosid.ctos.com.my/ctosid_new/edmPayment-bfslcheckout" target="_blank" rel="noopener noreferrer" class="font-semibold text-brandLuxuryDark underline decoration-brandGold">CTOS ID</a>.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col justify-center">
                                <label class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider mb-2">Personal Insurance
                                    ?</label>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="hasInsurance" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brandGold/25 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brandLuxuryDark">
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-brandLuxuryDark">Yes, I have insurance</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="button" onclick="goToStep1()"
                                class="flex-1 px-6 py-4 rounded-2xl font-semibold text-brandSlate hover:text-brandLuxuryDark hover:bg-slate-50 transition-colors">
                                Back
                            </button>
                            <button type="button" onclick="goToStep3()"
                                class="w-2/3 flex items-center justify-center gap-2 bg-brandLuxuryDark hover:bg-slate-800 text-white font-semibold py-4 rounded-2xl shadow-lg shadow-slate-900/10 transition-all transform active:scale-[0.98]">
                                <span>Next Step: Debt Service Ratio</span>
                                <i class="fa-solid fa-arrow-right text-xs text-brandGold"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Debt Service Ratio (DSR) -->
                <div id="step3" class="step-hidden max-w-2xl mx-auto">
                    <div class="space-y-8">
                        <div class="border-b border-slate-100 pb-4">
                            <span class="eyebrow-tag">Step 3 of 5</span>
                            <h2 class="mt-2 text-lg font-bold text-brandLuxuryDark">Debt Service Ratio (DSR)</h2>
                            <p class="text-sm text-brandSlate font-light">DSR is the percentage of your monthly income that goes
                                towards debt payments. A lower DSR indicates better financial health and higher
                                chances of loan approval.</p>
                            <div class="mt-4 mb-4 overflow-x-auto rounded-2xl border border-slate-200">
                                <table id="dsrTable" class="w-full text-sm border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 text-left">
                                            <th class="px-3 py-2.5 border-b border-slate-200 font-semibold text-brandLuxuryDark">DSR Range</th>
                                            <th class="px-3 py-2.5 border-b border-slate-200 font-semibold text-brandLuxuryDark">Status</th>
                                            <th class="px-3 py-2.5 border-b border-slate-200 font-semibold text-brandLuxuryDark">Bank Approval</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="row-below40" class="border-b border-slate-100">
                                            <td class="px-3 py-2.5">Below 40%</td>
                                            <td class="px-3 py-2.5">Excellent</td>
                                            <td class="px-3 py-2.5 text-brandSlate">Very High; generally considered low risk by Malaysian banks.</td>
                                        </tr>
                                        <tr id="row-40-60" class="border-b border-slate-100">
                                            <td class="px-3 py-2.5">40% - 60%</td>
                                            <td class="px-3 py-2.5">Good</td>
                                            <td class="px-3 py-2.5 text-brandSlate">High; standard for most middle-income earners.</td>
                                        </tr>
                                        <tr id="row-60-70" class="border-b border-slate-100">
                                            <td class="px-3 py-2.5">60% - 70%</td>
                                            <td class="px-3 py-2.5">Warning</td>
                                            <td class="px-3 py-2.5 text-brandSlate">Moderate; may require additional income proof.</td>
                                        </tr>
                                        <tr id="row-above70">
                                            <td class="px-3 py-2.5">Above 70%</td>
                                            <td class="px-3 py-2.5">Critical</td>
                                            <td class="px-3 py-2.5 text-brandSlate">Low; high risk of "over-gearing" rejection.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-sm text-brandSlate font-light">Please state your monthly commitments for the eligibility calculation.</p>
                        </div>

                        <div class="space-y-5">
                            <!-- Credit Card -->
                            <div class="group bg-white p-5 rounded-2xl border border-slate-100 premium-shadow hover:border-slate-200 transition-shadow">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2.5 bg-slate-50 rounded-xl">
                                            <i class="fa-regular fa-credit-card text-brandLuxuryDark"></i>
                                        </div>
                                        <span class="font-bold text-brandLuxuryDark">Credit Card Debt?</span>
                                    </div>
                                    <div class="flex bg-slate-100 p-1 rounded-2xl w-fit">
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="creditCard" data-value="yes">Yes</button>
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="creditCard" data-value="no">No</button>
                                    </div>
                                </div>
                                <div id="creditCardInstallmentDiv" class="hidden mt-6 pt-4 border-t border-dashed border-slate-200">
                                    <label class="block text-xs font-bold text-brandLuxuryDark uppercase tracking-wider mb-2">Monthly Installment</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-medium">RM</span>
                                        <input type="number" id="creditCardInstallment" class="dsr-installment-input w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-brandGold/15 focus:border-brandLuxuryDark outline-none transition-all placeholder:text-slate-300" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <!-- Car Loan -->
                            <div class="group bg-white p-5 rounded-2xl border border-slate-100 premium-shadow hover:border-slate-200 transition-shadow">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2.5 bg-slate-50 rounded-xl">
                                            <i class="fa-solid fa-car text-brandLuxuryDark"></i>
                                        </div>
                                        <span class="font-bold text-brandLuxuryDark">Car Loan? (Existing / Additional)</span>
                                    </div>
                                    <div class="flex bg-slate-100 p-1 rounded-2xl w-fit">
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="carLoan" data-value="yes">Yes</button>
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="carLoan" data-value="no">No</button>
                                    </div>
                                </div>
                                <div id="carLoanInstallmentDiv" class="hidden mt-6 pt-4 border-t border-dashed border-slate-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-xs font-bold text-brandLuxuryDark uppercase tracking-wider">Monthly Installment</label>
                                        <button type="button" onclick="addDsrInstallmentRow('carLoan')" class="text-sm font-semibold text-brandGoldHover hover:text-blue-600">+ Add More</button>
                                    </div>
                                    <div id="carLoanInstallmentList" class="space-y-3"></div>
                                </div>
                            </div>
                            <!-- Motorcycle Loan -->
                            <div class="group bg-white p-5 rounded-2xl border border-slate-100 premium-shadow hover:border-slate-200 transition-shadow">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2.5 bg-slate-50 rounded-xl">
                                            <i class="fa-solid fa-motorcycle text-brandLuxuryDark"></i>
                                        </div>
                                        <span class="font-bold text-brandLuxuryDark">Motorcycle Loan?</span>
                                    </div>
                                    <div class="flex bg-slate-100 p-1 rounded-2xl w-fit">
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="motorcycleLoan" data-value="yes">Yes</button>
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="motorcycleLoan" data-value="no">No</button>
                                    </div>
                                </div>
                                <div id="motorcycleLoanInstallmentDiv" class="hidden mt-6 pt-4 border-t border-dashed border-slate-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-xs font-bold text-brandLuxuryDark uppercase tracking-wider">Monthly Installment</label>
                                        <button type="button" onclick="addDsrInstallmentRow('motorcycleLoan')" class="text-sm font-semibold text-brandGoldHover hover:text-blue-600">+ Add More</button>
                                    </div>
                                    <div id="motorcycleLoanInstallmentList" class="space-y-3"></div>
                                </div>
                            </div>
                            <!-- House Loan -->
                            <div class="group bg-white p-5 rounded-2xl border border-slate-100 premium-shadow hover:border-slate-200 transition-shadow">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2.5 bg-slate-50 rounded-xl">
                                            <i class="fa-solid fa-house text-brandLuxuryDark"></i>
                                        </div>
                                        <span class="font-bold text-brandLuxuryDark">House Loan?</span>
                                    </div>
                                    <div class="flex bg-slate-100 p-1 rounded-2xl w-fit">
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="homeLoan" data-value="yes">Yes</button>
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="homeLoan" data-value="no">No</button>
                                    </div>
                                </div>
                                <div id="homeLoanInstallmentDiv" class="hidden mt-6 pt-4 border-t border-dashed border-slate-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-xs font-bold text-brandLuxuryDark uppercase tracking-wider">Monthly Installment</label>
                                        <button type="button" onclick="addDsrInstallmentRow('homeLoan')" class="text-sm font-semibold text-brandGoldHover hover:text-blue-600">+ Add More</button>
                                    </div>
                                    <div id="homeLoanInstallmentList" class="space-y-3"></div>
                                </div>
                            </div>
                            <!-- ASB Financing -->
                            <div class="group bg-white p-5 rounded-2xl border border-slate-100 premium-shadow hover:border-slate-200 transition-shadow">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2.5 bg-slate-50 rounded-xl">
                                            <i class="fa-solid fa-chart-line text-brandLuxuryDark"></i>
                                        </div>
                                        <span class="font-bold text-brandLuxuryDark">ASB Financing?</span>
                                    </div>
                                    <div class="flex bg-slate-100 p-1 rounded-2xl w-fit">
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="asbFinancing" data-value="yes">Yes</button>
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="asbFinancing" data-value="no">No</button>
                                    </div>
                                </div>
                                <div id="asbFinancingInstallmentDiv" class="hidden mt-6 pt-4 border-t border-dashed border-slate-200">
                                    <label class="block text-xs font-bold text-brandLuxuryDark uppercase tracking-wider mb-2">Monthly Installment</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-medium">RM</span>
                                        <input type="number" id="asbFinancingInstallment" class="dsr-installment-input w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-brandGold/15 focus:border-brandLuxuryDark outline-none transition-all placeholder:text-slate-300" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <!-- Education Loan -->
                            <div class="group bg-white p-5 rounded-2xl border border-slate-100 premium-shadow hover:border-slate-200 transition-shadow">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2.5 bg-slate-50 rounded-xl">
                                            <i class="fa-solid fa-graduation-cap text-brandLuxuryDark"></i>
                                        </div>
                                        <span class="font-bold text-brandLuxuryDark">Education Loan (PTPTN)?</span>
                                    </div>
                                    <div class="flex bg-slate-100 p-1 rounded-2xl w-fit">
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="educationLoan" data-value="yes">Yes</button>
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="educationLoan" data-value="no">No</button>
                                    </div>
                                </div>
                                <div id="educationLoanInstallmentDiv" class="hidden mt-6 pt-4 border-t border-dashed border-slate-200">
                                    <label class="block text-xs font-bold text-brandLuxuryDark uppercase tracking-wider mb-2">Monthly Installment</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-medium">RM</span>
                                        <input type="number" id="educationLoanInstallment" class="dsr-installment-input w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-brandGold/15 focus:border-brandLuxuryDark outline-none transition-all placeholder:text-slate-300" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <!-- Personal Loan -->
                            <div class="group bg-white p-5 rounded-2xl border border-slate-100 premium-shadow hover:border-slate-200 transition-shadow">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2.5 bg-slate-50 rounded-xl">
                                            <i class="fa-solid fa-money-check-dollar text-brandLuxuryDark"></i>
                                        </div>
                                        <span class="font-bold text-brandLuxuryDark">Personal Loan?</span>
                                    </div>
                                    <div class="flex bg-slate-100 p-1 rounded-2xl w-fit">
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="personalLoan" data-value="yes">Yes</button>
                                        <button type="button" class="dsr-btn px-6 py-2 rounded-xl font-bold text-sm transition-all duration-200 text-slate-400" data-loan="personalLoan" data-value="no">No</button>
                                    </div>
                                </div>
                                <div id="personalLoanInstallmentDiv" class="hidden mt-6 pt-4 border-t border-dashed border-slate-200">
                                    <label class="block text-xs font-bold text-brandLuxuryDark uppercase tracking-wider mb-2">Monthly Installment</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-medium">RM</span>
                                        <input type="number" id="personalLoanInstallment" class="dsr-installment-input w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-brandGold/15 focus:border-brandLuxuryDark outline-none transition-all placeholder:text-slate-300" placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div id="dsrSummary" class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-sm text-brandLuxuryDark font-semibold">
                            Current DSR: 0.0%
                        </div>


                        <div class="flex items-center gap-4 pt-6">
                            <button type="button" onclick="goToStep2Back()"
                                class="flex-1 px-6 py-4 rounded-2xl font-semibold text-brandSlate hover:text-brandLuxuryDark hover:bg-slate-50 transition-colors">
                                Back
                            </button>
                            <button type="button" onclick="goToStep4()"
                                class="flex-[2] flex items-center justify-center gap-2 bg-brandLuxuryDark hover:bg-slate-800 text-white font-semibold py-4 rounded-2xl shadow-lg shadow-slate-900/10 transition-all transform active:scale-[0.98]">
                                <span>Next: Car Ownership</span>
                                <i class="fa-solid fa-arrow-right text-xs text-brandGold"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 4: CCRIS Review -->
                <div id="step4" class="step-hidden">
                    <div class="space-y-6">
                        <div class="border-b border-slate-100 pb-4">
                            <span class="eyebrow-tag">Step 4 of 5</span>
                            <h2 class="mt-2 text-lg font-bold text-brandLuxuryDark">CCRIS Review</h2>
                            <p class="text-sm text-brandSlate font-light">For every active debt from your DSR list, enter how many months were in arrears over the last 12 months.</p>
                            <div class="mt-3 rounded-2xl border border-blue-200 bg-blue-50 p-3.5 text-sm text-blue-900">
                                <p class="font-semibold">Please review your CCRIS record before completing this section.</p>
                                <p class="mt-1">
                                    CCRIS (Central Credit Reference Information System) is maintained by Bank Negara Malaysia (BNM) and contains information on your credit
                                    facilities and repayment history. Please ensure that all loan and financing commitments entered here match your CCRIS record. Providing
                                    inaccurate or incomplete information may result in an incorrect assessment.
                                </p>
                                <p class="mt-1">
                                    You can check your CCRIS report through BNM here:
                                    <a href="https://www.bnm.gov.my/ccris" target="_blank" rel="noopener noreferrer" class="underline">
                                        https://www.bnm.gov.my/ccris
                                    </a>
                                </p>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-white p-4 premium-shadow">
                            <div class="flex items-center justify-between gap-4 mb-4">
                                <div>
                                    <h3 class="text-base font-bold text-brandLuxuryDark">Sample CCRIS Report Snapshot</h3>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-brandLuxuryDark">Preview</span>
                            </div>
                            <p class="text-sm text-brandSlate font-light mb-4">
                                Refer to this example CCRIS report layout while entering arrears months for each active debt. Match the entries to the actual report values shown here.
                            </p>
                            <div class="flex flex-col gap-3">
                                <button id="ccrisExampleOpenBtn" type="button"
                                    class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 cursor-zoom-in transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-brandGold">
                                    <img src="Images/CCRIS2.jpg" alt="CCRIS report example" class="w-full h-auto object-cover">
                                </button>
                            </div>
                        </div>

                        <div id="ccrisLoanSections" class="space-y-4"></div>
                        <p id="ccrisValidationMessage" class="hidden rounded-2xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700"></p>

                        <div class="flex gap-2 pt-4">
                            <button type="button" onclick="goToStep3Back()"
                                class="flex-1 px-6 py-4 rounded-2xl font-semibold text-brandSlate hover:text-brandLuxuryDark hover:bg-slate-50 transition-colors">
                                Back
                            </button>
                            <button type="button" onclick="goToStep5()"
                                class="w-2/3 flex items-center justify-center gap-2 bg-brandLuxuryDark hover:bg-slate-800 text-white font-semibold py-4 rounded-2xl shadow-lg shadow-slate-900/10 transition-all transform active:scale-[0.98]">
                                <span>Next: Car Ownership</span>
                                <i class="fa-solid fa-arrow-right text-xs text-brandGold"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Car Ownership History -->
                <div id="step5" class="step-hidden">
                    <div class="space-y-6">
                        <div class="border-b border-slate-100 pb-4">
                            <span class="eyebrow-tag">Step 5 of 5 &middot; Final Step</span>
                            <h2 class="mt-2 text-lg font-bold text-brandLuxuryDark">Car Ownership &amp; NCD</h2>
                            <p class="text-sm text-brandSlate font-light">Last step — tell us about any No Claim Discount you'd like to carry over.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider mb-3">Would you like to transfer
                                NCD (No Claim Discount)?</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" onclick="toggleNCDDetails(true)" id="ncdYes"
                                    class="dsr-btn p-4 border-2 border-slate-200 rounded-2xl hover:bg-slate-50 transition font-bold text-brandLuxuryDark text-sm">
                                    Yes
                                </button>
                                <button type="button" onclick="toggleNCDDetails(false)" id="ncdNo"
                                    class="dsr-btn p-4 border-2 border-slate-200 rounded-2xl hover:bg-slate-50 transition font-bold text-brandLuxuryDark text-sm">
                                    No
                                </button>
                            </div>
                        </div>

                        <div id="ncdDropdown" class="hidden space-y-2">
                            <label class="block text-xs font-semibold text-brandLuxuryDark uppercase tracking-wider">Current NCD
                                Percentage</label>
                            <select id="ncdPercentage"
                                class="input-glow w-full p-3.5 border border-slate-200 rounded-2xl bg-slate-50/50 outline-none text-sm text-brandLuxuryDark font-medium appearance-none cursor-pointer">
                                <option value="" selected disabled>Select your NCD year&hellip;</option>
                                <option value="0">Year 1 (0%)</option>
                                <option value="25">Year 2 (25%)</option>
                                <option value="30">Year 3 (30%)</option>
                                <option value="38">Year 4 (38%)</option>
                                <option value="45">Year 5 (45%)</option>
                            </select>
                        </div>

                        <p id="ncdValidationMessage" class="hidden rounded-2xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700"></p>

                        <div class="flex gap-2 pt-4">
                            <button type="button" onclick="goToStep4Back()"
                                class="flex-1 px-6 py-4 rounded-2xl font-semibold text-brandSlate hover:text-brandLuxuryDark hover:bg-slate-50 transition-colors">
                                Back
                            </button>
                            <button type="submit" id="finalSubmitBtn"
                                class="hidden w-2/3 flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-4 rounded-2xl shadow-lg transition-all transform active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed">
                                Submit
                            </button>
                        </div>
                    </div>
                </div>

            </form>

            <!-- Scoring Loading Overlay: shown briefly after Submit while the score is "calculated" -->
            <div id="scoreLoadingOverlay" class="fixed inset-0 z-[60] hidden items-center justify-center bg-brandLuxuryDark/60 backdrop-blur-sm">
                <div class="w-full max-w-sm rounded-3xl bg-white p-8 text-center premium-shadow mx-4">
                    <div class="mx-auto mb-5 h-14 w-14 animate-spin rounded-full border-4 border-slate-100 border-t-brandGold"></div>
                    <h3 class="text-base font-bold text-brandLuxuryDark">Analyzing your profile&hellip;</h3>
                    <p id="scoreLoadingStatus" class="mt-2 text-sm text-brandSlate font-light">Reading your financial details&hellip;</p>
                </div>
            </div>

            <!-- ============================= -->
            <!-- REDESIGNED RESULT BOX (Modal Popup) -->
            <!-- ============================= -->
            <!-- Modal Backdrop -->
            <div id="resultBoxBackdrop" class="fixed inset-0 z-40 hidden bg-brandLuxuryDark/50 backdrop-blur-sm transition-opacity duration-300"></div>

            <!-- Modal Content -->
            <div id="resultBox" class="fixed inset-0 z-50 hidden overflow-y-auto">
                <div class="flex min-h-screen items-center justify-center p-3 sm:p-4">
                    <div class="relative w-full max-w-xl rounded-2xl sm:rounded-3xl border border-slate-200 bg-white premium-shadow overflow-hidden">

                        <div class="absolute top-0 left-0 right-0 h-[4px] bg-gradient-to-r from-brandGold via-blue-400 to-brandGold z-10"></div>

                        <!-- Banner: icon + status stack on every breakpoint, no awkward mid-size wrapping -->
                        <div id="resultBanner" class="flex items-start gap-2 sm:gap-3 border-b border-slate-100 bg-emerald-50 p-4 sm:p-4 pt-6">
                            <span id="resultBannerIcon"
                                class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-sm sm:text-base font-bold text-white">✓</span>
                            <div class="min-w-0">
                                <p id="resultBannerText"
                                    class="text-xs sm:text-sm font-bold uppercase tracking-wide leading-snug text-emerald-900">
                                    STATUS: PASSED (Eligible for Vehicle Recommendations)</p>
                                <p id="resultBannerSubtext" class="mt-0.5 text-xs text-emerald-700/80">
                                    Your application profile is ready for the next step.</p>
                            </div>
                        </div>

                        <!-- Body: Financial Metrics on top, Affordability Limits below — always stacked -->
                        <div class="space-y-3 p-4 sm:space-y-3 sm:p-4">

                            <!-- Financial Metrics -->
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:rounded-2xl sm:p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 sm:text-xs">Financial Metrics</p>
                                        <h2 class="mt-0.5 text-lg font-bold text-brandLuxuryDark sm:text-xl">Readiness snapshot</h2>
                                    </div>
                                    <div class="whitespace-nowrap rounded-full bg-brandGold/15 px-2 py-1 text-[10px] font-bold text-blue-700 sm:text-xs">
                                        Premium Bank View</div>
                                </div>

                                <div class="mt-4 space-y-3 sm:mt-4 sm:space-y-3">
                                    <div>
                                        <div class="mb-1.5 flex items-center justify-between text-xs text-slate-500">
                                            <span>Debt Service Ratio (DSR)</span>
                                            <span id="dsrPercentValue" class="font-semibold text-brandLuxuryDark">0%</span>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-slate-200 sm:h-2">
                                            <div id="dsrProgressBar" class="h-full w-0 rounded-full bg-emerald-500 transition-all duration-500"></div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-2">
                                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                                            <p class="text-[9px] uppercase tracking-[0.15em] text-slate-400 sm:text-[10px]">Credit Status</p>
                                            <p id="creditStatusText" class="mt-1.5 text-xs font-semibold text-emerald-600 sm:mt-2 sm:text-sm">
                                                Clean / No Arrears</p>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                                            <p class="text-[9px] uppercase tracking-[0.15em] text-slate-400 sm:text-[10px]">Loan Approval Probability</p>
                                            <p id="approvalProbabilityText" class="mt-1.5 text-xs font-semibold text-emerald-600 sm:mt-2 sm:text-sm">
                                                High Likelihood</p>
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-slate-200 bg-white p-3.5 sm:p-4">
                                        <p class="text-[9px] uppercase tracking-[0.15em] text-slate-400 sm:text-[10px]">Overall Financial Readiness Score</p>
                                        <p id="readinessScoreValue" class="mt-2 text-3xl font-black text-brandLuxuryDark sm:mt-2 sm:text-4xl">95 / 100</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Affordability -->
                            <div id="affordabilitySection" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:rounded-2xl sm:p-4">
                                <div>
                                    <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 sm:text-xs">Affordability Limits</p>
                                    <h2 class="mt-0.5 text-lg font-bold text-brandLuxuryDark sm:text-xl">Recommended budget</h2>
                                </div>
                                <div class="mt-4 grid grid-cols-1 gap-2 sm:mt-4 sm:grid-cols-2 sm:gap-2">
                                    <div class="rounded-xl border border-slate-200 bg-white p-3.5 sm:p-4">
                                        <p class="text-[9px] uppercase tracking-[0.15em] text-slate-400 sm:text-[10px]">Maximum Affordable Vehicle Price</p>
                                        <p id="affordablePriceValue" class="mt-2 text-2xl font-black text-brandGoldHover sm:mt-2 sm:text-3xl">RM 0</p>
                                    </div>
                                    <div class="rounded-xl border border-slate-200 bg-white p-3.5 sm:p-4">
                                        <p class="text-[9px] uppercase tracking-[0.15em] text-slate-400 sm:text-[10px]">Estimated Max Monthly Repayment</p>
                                        <p id="monthlyCapValue" class="mt-2 text-lg font-bold text-brandLuxuryDark sm:mt-2 sm:text-2xl">RM 720 / month</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Close Button -->
                        <button id="resultBoxCloseBtn" class="absolute right-3 top-3 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-100 hover:text-brandLuxuryDark">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>

                        <div class="border-t border-slate-100 px-4 py-4 text-center sm:px-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:justify-center">
                                <button id="printReportBtn" type="button"
                                    class="hidden rounded-full border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-brandLuxuryDark shadow-sm transition duration-300 hover:-translate-y-0.5 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-brandGold/20 sm:px-6 sm:py-3 sm:text-sm">
                                    Print Report</button>
                                <a id="viewCarListBtn" href="#" target="_blank" rel="noopener noreferrer"
                                    class="hidden flex items-center justify-center gap-2 rounded-full bg-brandLuxuryDark px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-slate-900/10 transition duration-300 hover:-translate-y-0.5 hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-brandGold/25 sm:px-6 sm:py-3 sm:text-sm">
                                    Proceed to View Recommended Cars <i class="fa-solid fa-arrow-right text-brandGold text-[10px]"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CCRIS Example Modal Backdrop -->
            <div id="ccrisExampleBackdrop" class="fixed inset-0 z-40 hidden bg-brandLuxuryDark/50 backdrop-blur-sm transition-opacity duration-300"></div>

            <!-- CCRIS Example Modal -->
            <div id="ccrisExampleModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="relative w-full max-w-3xl rounded-3xl border border-slate-200 bg-white premium-shadow">
                        <div class="absolute top-0 left-0 right-0 h-[4px] bg-gradient-to-r from-brandGold via-blue-400 to-brandGold z-10 rounded-t-3xl"></div>
                        <div class="flex items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 p-4 pt-6 rounded-t-3xl">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">CCRIS Example</p>
                                <h3 class="text-lg font-bold text-brandLuxuryDark">Enlarged CCRIS Report Preview</h3>
                                <p class="mt-1 text-sm text-brandSlate font-light">Tap close when you have finished comparing values.</p>
                            </div>
                            <button id="ccrisExampleCloseBtn" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-100 hover:text-brandLuxuryDark">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="p-4 sm:p-6">
                            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-100">
                                <img src="Images/CCRIS2.jpg" alt="CCRIS report example" class="w-full h-auto object-contain">
                            </div>
                            <p class="mt-4 text-sm text-brandSlate font-light">This enlarged CCRIS report snapshot helps you review the arrears values clearly before completing Step 4.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="max-w-2xl mx-auto pb-10 text-center text-xs text-slate-400">
            <div>© 2026 DriveSmart. Premium Auto Finance Assessment. All rights reserved.</div>
        </footer>

        <script src="recommendation.js?v=<?php echo filemtime(__DIR__ . '/Recommendation.js'); ?>"></script>
        <script src="timezone-widget.js"></script>
</body>

</html>
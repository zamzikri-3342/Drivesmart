const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            const step3 = document.getElementById('step3');
            const step4 = document.getElementById('step4');
            const step5 = document.getElementById('step5');
            const stepIndicator = document.getElementById('stepIndicator');
            const icInput = document.getElementById('ic');
            const ageInput = document.getElementById('age');
            const pekerjaanSelect = document.getElementById('pekerjaan');
            const jobType = document.getElementById('jobType');
            const printReportBtn = document.getElementById('printReportBtn');

            // ---- Journey progress stepper ----
            const STEP_LABELS = {
                1: 'Personal & Employment Details',
                2: 'Financial Details',
                3: 'Debt Service Ratio (DSR)',
                4: 'CCRIS / CTOS Review',
                5: 'NCD & Car Ownership'
            };
            const journeyFill = document.getElementById('journeyFill');
            const journeyCar = document.getElementById('journeyCar');

            function updateJourney(stepNumber) {
                for (let i = 1; i <= 5; i++) {
                    const dot = document.getElementById(`stepDot${i}`);
                    const label = document.getElementById(`stepLabel${i}`);
                    if (!dot) continue;
                    dot.classList.remove('pending', 'active', 'done');
                    label?.classList.remove('active');
                    if (i < stepNumber) {
                        dot.classList.add('done');
                        dot.innerHTML = '✓';
                    } else if (i === stepNumber) {
                        dot.classList.add('active');
                        dot.innerText = i;
                        label?.classList.add('active');
                    } else {
                        dot.classList.add('pending');
                        dot.innerText = i;
                    }
                }
                const pct = ((stepNumber - 1) / 4) * 100;
                if (journeyFill) journeyFill.style.width = `${pct}%`;
                if (journeyCar) journeyCar.style.left = `${pct}%`;
                if (stepIndicator) {
                    stepIndicator.innerHTML = `Step ${stepNumber} of 5 &middot; ${STEP_LABELS[stepNumber]}`;
                }
            }

            // Navigation Functions
            function goToStep2() {
                if (icInput.value.length < 14) {
                    document.getElementById('icError').classList.remove('hidden');
                    icInput.focus();
                    return;
                }
                if (!document.getElementById('nama').value || !pekerjaanSelect.value) {
                    alert("Please fill in all required fields.");
                    return;
                }
                step1.classList.add('step-hidden');
                step2.classList.remove('step-hidden');
                updateJourney(2);
                closeResultModal();
                window.scrollTo(0, 0);
            }

            function goToStep1() {
                step2.classList.add('step-hidden');
                step3.classList.add('step-hidden');
                step4.classList.add('step-hidden');
                step5.classList.add('step-hidden');
                step1.classList.remove('step-hidden');
                closeResultModal();
                updateJourney(1);
                window.scrollTo(0, 0);
            }

            function goToStep3() {
                // 1. Basic Validation for Step 2
                const gaji = document.getElementById('gaji').value;
                const creditScore = document.getElementById('creditScore').value;
                const loanYears = document.getElementById('loanYears').value;

                if (!gaji || !creditScore || !loanYears) {
                    alert("Sila lengkapkan maklumat kewangan anda.");
                    return;
                }

                step2.classList.add('step-hidden');
                step3.classList.remove('step-hidden');
                step4.classList.add('step-hidden');
                step5.classList.add('step-hidden');
                updateJourney(3);
                window.scrollTo(0, 0);
            }

            function buildCcrisSections() {
                const container = document.getElementById('ccrisLoanSections');
                const validationMessage = document.getElementById('ccrisValidationMessage');
                if (!container) return;
                if (validationMessage) {
                    validationMessage.classList.add('hidden');
                    validationMessage.innerText = '';
                }

                const loanDefinitions = [{
                        key: 'creditCard',
                        label: 'Credit Card Debt'
                    },
                    {
                        key: 'carLoan',
                        label: 'Car Loan'
                    },
                    {
                        key: 'motorcycleLoan',
                        label: 'Motorcycle Loan'
                    },
                    {
                        key: 'homeLoan',
                        label: 'House Loan'
                    },
                    {
                        key: 'asbFinancing',
                        label: 'ASB Financing'
                    },
                    {
                        key: 'educationLoan',
                        label: 'Education Loan'
                    },
                    {
                        key: 'personalLoan',
                        label: 'Personal Loan'
                    }
                ];

                const activeLoans = loanDefinitions.filter(loan => {
                    const yesBtn = document.querySelector(`.dsr-btn[data-loan="${loan.key}"][data-value="yes"]`);
                    return yesBtn?.classList.contains('bg-brandGold/15');
                });

                if (activeLoans.length === 0) {
                    container.innerHTML = '<div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Tiada pinjaman aktif dari DSR untuk dipaparkan di CCRIS.</div>';
                    return;
                }

                container.innerHTML = activeLoans.map(loan => `
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="mb-3 font-semibold text-gray-800">${loan.label}</div>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <label class="text-xs font-medium text-gray-600">
                                <span class="mb-1 block">0 bulan</span>
                                <input type="number" min="0" step="1" value="0" data-loan-key="${loan.key}" data-arrears="0" class="w-full rounded-lg border border-gray-200 p-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </label>
                            <label class="text-xs font-medium text-gray-600">
                                <span class="mb-1 block">1 bulan</span>
                                <input type="number" min="0" step="1" value="0" data-loan-key="${loan.key}" data-arrears="1" class="w-full rounded-lg border border-gray-200 p-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </label>
                            <label class="text-xs font-medium text-gray-600">
                                <span class="mb-1 block">2 bulan</span>
                                <input type="number" min="0" step="1" value="0" data-loan-key="${loan.key}" data-arrears="2" class="w-full rounded-lg border border-gray-200 p-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </label>
                            <label class="text-xs font-medium text-gray-600">
                                <span class="mb-1 block">3 bulan</span>
                                <input type="number" min="0" step="1" value="0" data-loan-key="${loan.key}" data-arrears="3" class="w-full rounded-lg border border-gray-200 p-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </label>
                            <label class="text-xs font-medium text-gray-600">
                                <span class="mb-1 block">4 bulan</span>
                                <input type="number" min="0" step="1" value="0" data-loan-key="${loan.key}" data-arrears="4" class="w-full rounded-lg border border-gray-200 p-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </label>
                            <label class="text-xs font-medium text-gray-600">
                                <span class="mb-1 block">5+ bulan</span>
                                <input type="number" min="0" step="1" value="0" data-loan-key="${loan.key}" data-arrears="5" class="w-full rounded-lg border border-gray-200 p-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </label>
                        </div>
                    </div>
                `).join('');
            }

            function createDsrInstallmentRow(loanKey) {
                const row = document.createElement('div');
                row.className = 'flex gap-2 items-start dsr-installment-row';
                row.innerHTML = `
                    <div class="relative flex-1">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">RM</span>
                        <input type="number" class="dsr-installment-input w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-50 focus:border-blue-500 outline-none transition-all placeholder:text-gray-300" placeholder="0.00">
                    </div>
                    <button type="button" class="text-sm font-semibold text-red-500 hover:text-red-700 whitespace-nowrap" data-remove-row="${loanKey}">Remove</button>
                `;

                row.querySelector('button')?.addEventListener('click', function() {
                    row.remove();
                    calculateDSR();
                });

                return row;
            }

            function addDsrInstallmentRow(loanKey) {
                const list = document.getElementById(`${loanKey}InstallmentList`);
                if (!list) return;
                list.appendChild(createDsrInstallmentRow(loanKey));
                calculateDSR();
            }

            function clearDsrInstallmentRows(loanKey) {
                const list = document.getElementById(`${loanKey}InstallmentList`);
                if (!list) return;
                list.innerHTML = '';
            }

            function getDsrInstallmentValues(loanKey) {
                const list = document.getElementById(`${loanKey}InstallmentList`);
                if (!list) {
                    const singleInput = document.getElementById(`${loanKey}Installment`);
                    return singleInput ? [parseFloat(singleInput.value) || 0] : [0];
                }

                return Array.from(list.querySelectorAll('.dsr-installment-input'))
                    .map(input => parseFloat(input.value) || 0);
            }

            function hasDsrInstallmentValue(loanKey) {
                return getDsrInstallmentValues(loanKey).some(value => value > 0);
            }

            function calculateDSR() {
                const salary = parseFloat(document.getElementById('gaji').value) || 0;
                const loanKeys = ['creditCard', 'carLoan', 'motorcycleLoan', 'homeLoan', 'asbFinancing', 'educationLoan', 'personalLoan'];
                let totalInstallment = 0;

                loanKeys.forEach(key => {
                    const yesBtn = document.querySelector(`.dsr-btn[data-loan="${key}"][data-value="yes"]`);
                    if (yesBtn && yesBtn.classList.contains('bg-brandGold/15')) {
                        totalInstallment += getDsrInstallmentValues(key).reduce((sum, value) => sum + value, 0);
                    }
                });

                const dsrPercent = salary > 0 ? (totalInstallment / salary) * 100 : 0;
                const summary = document.getElementById('dsrSummary');
                if (summary) {
                    summary.innerText = `DSR Semasa: ${dsrPercent.toFixed(1)}%`;
                    summary.classList.remove('bg-yellow-100', 'text-yellow-800', 'bg-red-100', 'text-red-800', 'bg-green-50', 'text-green-800');
                    if (dsrPercent > 70) {
                        summary.classList.add('bg-red-100', 'text-red-800');
                    } else if (dsrPercent >= 60) {
                        summary.classList.add('bg-yellow-100', 'text-yellow-800');
                    } else if (dsrPercent < 40 && dsrPercent > 0) {
                        summary.classList.add('bg-green-50', 'text-green-800');
                    }
                }
                updateDSRTable(dsrPercent);
                return dsrPercent;
            }

            // Update the reference table row highlight based on current DSR
            function updateDSRTable(dsr) {
                const rows = ['row-below40', 'row-40-60', 'row-60-70', 'row-above70'];
                rows.forEach(id => {
                    const r = document.getElementById(id);
                    if (!r) return;
                    r.classList.remove('bg-yellow-100', 'border-yellow-300', 'bg-red-100', 'border-red-300');
                });

                if (dsr > 70) {
                    const r = document.getElementById('row-above70');
                    if (r) r.classList.add('bg-red-100', 'border-red-300');
                } else if (dsr >= 60) {
                    const r = document.getElementById('row-60-70');
                    if (r) r.classList.add('bg-yellow-100', 'border-yellow-300');
                }
            }

            // Step 3 (DSR) → Step 4 (CCRIS / CTOS)
            function goToStep4() {
                calculateDSR();
                const dsrLoans = [{
                        key: 'creditCard',
                        label: 'hutang kad kredit'
                    },
                    {
                        key: 'carLoan',
                        label: 'pinjaman kereta'
                    },
                    {
                        key: 'motorcycleLoan',
                        label: 'pinjaman motosikal'
                    },
                    {
                        key: 'homeLoan',
                        label: 'pinjaman rumah'
                    },
                    {
                        key: 'asbFinancing',
                        label: 'ASB financing'
                    },
                    {
                        key: 'educationLoan',
                        label: 'pinjaman pendidikan'
                    },
                    {
                        key: 'personalLoan',
                        label: 'pinjaman peribadi'
                    }
                ];
                for (const loan of dsrLoans) {
                    const yesBtn = document.querySelector(`.dsr-btn[data-loan="${loan.key}"][data-value="yes"]`);
                    const noBtn = document.querySelector(`.dsr-btn[data-loan="${loan.key}"][data-value="no"]`);
                    const yesActive = yesBtn?.classList.contains('bg-brandGold/15');
                    const noActive = noBtn?.classList.contains('bg-brandGold/15');
                    if (!yesActive && !noActive) {
                        alert(`Sila jawab soalan ${loan.label}.`);
                        return;
                    }
                    if (yesActive && !hasDsrInstallmentValue(loan.key)) {
                        alert(`Sila isi ansuran bulanan untuk ${loan.label}.`);
                        const firstInput = document.querySelector(`#${loan.key}InstallmentList .dsr-installment-input, #${loan.key}Installment`);
                        firstInput?.focus();
                        return;
                    }
                }
                step3.classList.add('step-hidden');
                step4.classList.remove('step-hidden');
                step5.classList.add('step-hidden');
                buildCcrisSections();
                updateJourney(4);
                window.scrollTo(0, 0);
            }

            function validateCcrisEntries() {
                const validationMessage = document.getElementById('ccrisValidationMessage');
                const sections = document.querySelectorAll('#ccrisLoanSections [data-loan-key]');
                const activeLoans = Array.from(document.querySelectorAll('#ccrisLoanSections .rounded-2xl'));

                if (activeLoans.length === 0) {
                    if (validationMessage) {
                        validationMessage.classList.add('hidden');
                        validationMessage.innerText = '';
                    }
                    return true;
                }

                const totals = {};
                sections.forEach(input => {
                    const loanKey = input.getAttribute('data-loan-key');
                    const value = parseInt(input.value, 10) || 0;
                    totals[loanKey] = (totals[loanKey] || 0) + value;
                });

                const invalidLoan = Object.entries(totals).find(([, total]) => total !== 12);
                if (invalidLoan) {
                    const loanLabel = document.querySelector(`#ccrisLoanSections [data-loan-key="${invalidLoan[0]}"]`)?.closest('.rounded-2xl')?.querySelector('.font-semibold')?.innerText || 'This loan';
                    if (validationMessage) {
                        validationMessage.innerText = `${loanLabel} must total exactly 12 months across the 6 boxes. Please review your CCRIS report and correct the values.`;
                        validationMessage.classList.remove('hidden');
                    }
                    return false;
                }

                if (validationMessage) {
                    validationMessage.classList.add('hidden');
                    validationMessage.innerText = '';
                }
                return true;
            }

            function goToStep5() {
                if (!validateCcrisEntries()) {
                    return;
                }
                step4.classList.add('step-hidden');
                step5.classList.remove('step-hidden');
                updateJourney(5);
                window.scrollTo(0, 0);
            }

            // Back from Step 3 (DSR) to Step 2
            function goToStep2Back() {
                step3.classList.add('step-hidden');
                step4.classList.add('step-hidden');
                step5.classList.add('step-hidden');
                step2.classList.remove('step-hidden');
                updateJourney(2);
                closeResultModal();
                window.scrollTo(0, 0);
            }

            // Back from Step 4 (CCRIS) to Step 3
            function goToStep3Back() {
                step4.classList.add('step-hidden');
                step3.classList.remove('step-hidden');
                updateJourney(3);
                closeResultModal();
                window.scrollTo(0, 0);
            }

            // Back from Step 5 to Step 4
            function goToStep4Back() {
                step5.classList.add('step-hidden');
                step4.classList.remove('step-hidden');
                buildCcrisSections();
                updateJourney(4);
                closeResultModal();
                window.scrollTo(0, 0);
            }

            // Step 4 validation (Car Ownership History)
            // Prevent submit if not answered
            // Modal close functionality
            function closeResultModal() {
                document.getElementById('resultBox').classList.add('hidden');
                document.getElementById('resultBoxBackdrop').classList.add('hidden');
                document.body.style.overflow = '';
            }

            function openCcrisExampleModal() {
                document.getElementById('ccrisExampleModal')?.classList.remove('hidden');
                document.getElementById('ccrisExampleBackdrop')?.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeCcrisExampleModal() {
                document.getElementById('ccrisExampleModal')?.classList.add('hidden');
                document.getElementById('ccrisExampleBackdrop')?.classList.add('hidden');
                document.body.style.overflow = '';
            }

            document.getElementById('resultBoxCloseBtn')?.addEventListener('click', closeResultModal);
            document.getElementById('resultBoxBackdrop')?.addEventListener('click', closeResultModal);
            document.getElementById('ccrisExampleCloseBtn')?.addEventListener('click', closeCcrisExampleModal);
            document.getElementById('ccrisExampleBackdrop')?.addEventListener('click', closeCcrisExampleModal);
            document.getElementById('ccrisExampleOpenBtn')?.addEventListener('click', openCcrisExampleModal);

            printReportBtn?.addEventListener('click', function() {
                const reportData = buildReportData();
                generateReportPdf(reportData);
            });

            // ============================================================
            // Forward-Chaining Rule Engine
            // ------------------------------------------------------------
            // `facts` is the working memory: it starts with the raw inputs
            // read from the form and grows as rules fire. A rule only fires
            // once its IF condition is satisfied by the CURRENT facts, and
            // its THEN clause writes a new fact back into working memory —
            // which can then satisfy OTHER rules (e.g. the total-score rule
            // needs every sub-score fact to exist before it can fire, and
            // the eligibility/approval rules need the total-score fact).
            // The engine keeps sweeping the rule base until a full pass
            // derives no new facts (a fixpoint), which is what makes this
            // forward chaining rather than a fixed, hardcoded call order.
            // ============================================================
            function runForwardChaining(facts, rules) {
                const fired = new Set();
                let changed = true;
                while (changed) {
                    changed = false;
                    for (const rule of rules) {
                        if (fired.has(rule.id)) continue;
                        if (rule.if(facts)) {
                            rule.then(facts);
                            fired.add(rule.id);
                            changed = true;
                        }
                    }
                }
                return facts;
            }

            // ---- DSR sub-score rules (40 marks) — exhaustive bands ----
            const DSR_SCORE_RULES = [
                { id: 'dsr-1', if: f => f.dsrPercent <= 40, then: f => f.dsrScore = 40 },
                { id: 'dsr-2', if: f => f.dsrPercent > 40 && f.dsrPercent <= 45, then: f => f.dsrScore = 37 },
                { id: 'dsr-3', if: f => f.dsrPercent > 45 && f.dsrPercent <= 50, then: f => f.dsrScore = 33 },
                { id: 'dsr-4', if: f => f.dsrPercent > 50 && f.dsrPercent <= 55, then: f => f.dsrScore = 30 },
                { id: 'dsr-5', if: f => f.dsrPercent > 55 && f.dsrPercent <= 60, then: f => f.dsrScore = 27 },
                { id: 'dsr-6', if: f => f.dsrPercent > 60 && f.dsrPercent <= 64, then: f => f.dsrScore = 21 },
                { id: 'dsr-7', if: f => f.dsrPercent > 64 && f.dsrPercent <= 69, then: f => f.dsrScore = 10 },
                { id: 'dsr-8', if: f => f.dsrPercent > 69, then: f => f.dsrScore = 0 },
            ];

            // ---- Full eligibility rule base (evaluated on final submit) ----
            const ELIGIBILITY_RULES = [
                ...DSR_SCORE_RULES,

                // ---- CCRIS sub-score (30 marks) ----
                { id: 'ccris-1', if: f => f.ccrisMaxArrears <= 0, then: f => f.ccrisScore = 30 },
                { id: 'ccris-2', if: f => f.ccrisMaxArrears === 1, then: f => f.ccrisScore = 20 },
                { id: 'ccris-3', if: f => f.ccrisMaxArrears > 1 && f.ccrisMaxArrears <= 3, then: f => f.ccrisScore = 10 },
                { id: 'ccris-4', if: f => f.ccrisMaxArrears > 3, then: f => f.ccrisScore = 0 },

                // ---- CTOS sub-score (15 marks) ----
                { id: 'ctos-1', if: f => f.creditScoreValue === 'excellent', then: f => f.ctosScore = 15 },
                { id: 'ctos-2', if: f => f.creditScoreValue === 'good', then: f => f.ctosScore = 12 },
                { id: 'ctos-3', if: f => f.creditScoreValue === 'fair', then: f => f.ctosScore = 8 },
                { id: 'ctos-4', if: f => f.creditScoreValue === 'poor', then: f => f.ctosScore = 0 },
                { id: 'ctos-5', if: f => !['excellent', 'good', 'fair', 'poor'].includes(f.creditScoreValue), then: f => f.ctosScore = 0 },

                // ---- Employment stability sub-score (5 marks) ----
                { id: 'emp-1', if: f => f.jobSector === 'gov', then: f => f.employmentScore = 5 },
                { id: 'emp-2', if: f => f.jobSector === 'private' && f.jobType === 'permanent', then: f => f.employmentScore = 4 },
                { id: 'emp-3', if: f => f.jobSector === 'private' && f.jobType === 'contract', then: f => f.employmentScore = 2 },
                { id: 'emp-4', if: f => f.jobSector === 'self' || f.jobSector === 'pesara', then: f => f.employmentScore = 1 },
                { id: 'emp-5', if: f => f.jobSector === 'private' && f.jobType !== 'permanent' && f.jobType !== 'contract', then: f => f.employmentScore = 0 },
                { id: 'emp-6', if: f => !['gov', 'private', 'self', 'pesara'].includes(f.jobSector), then: f => f.employmentScore = 0 },

                // ---- Existing loan commitments sub-score (5 marks) ----
                { id: 'loan-1', if: f => f.loanCount <= 2, then: f => f.loanCommitmentScore = 5 },
                { id: 'loan-2', if: f => f.loanCount > 2 && f.loanCount <= 4, then: f => f.loanCommitmentScore = 3 },
                { id: 'loan-3', if: f => f.loanCount > 4, then: f => f.loanCommitmentScore = 0 },

                // ---- Insurance coverage sub-score (2 marks) ----
                { id: 'ins-1', if: f => f.hasInsurance === true, then: f => f.insuranceScore = 2 },
                { id: 'ins-2', if: f => f.hasInsurance === false, then: f => f.insuranceScore = 0 },

                // ---- NCD sub-score (3 marks) ----
                { id: 'ncd-1', if: f => f.ncdYesActive && f.ncdPercentage >= 25, then: f => f.ncdScore = 3 },
                { id: 'ncd-2', if: f => !(f.ncdYesActive && f.ncdPercentage >= 25), then: f => f.ncdScore = 0 },

                // ---- Derived: total readiness score — only fires once ALL 7 sub-score facts exist ----
                {
                    id: 'total-score',
                    if: f => [f.dsrScore, f.ccrisScore, f.ctosScore, f.employmentScore, f.loanCommitmentScore, f.insuranceScore, f.ncdScore]
                        .every(v => v !== undefined),
                    then: f => {
                        f.totalScore = f.dsrScore + f.ccrisScore + f.ctosScore + f.employmentScore +
                            f.loanCommitmentScore + f.insuranceScore + f.ncdScore;
                    }
                },

                // ---- Derived: eligibility pass/fail — only fires once totalScore exists ----
                {
                    id: 'eligibility',
                    if: f => f.totalScore !== undefined,
                    then: f => {
                        f.passStatus = (f.totalScore >= 70) && (f.dsrScore >= 20) && (f.ccrisScore >= 20);
                    }
                },

                // ---- Derived: approval probability text — only fires once totalScore exists ----
                {
                    id: 'approval-probability',
                    if: f => f.totalScore !== undefined,
                    then: f => {
                        if (f.projectedDsrPercent > 80) f.approvalProbabilityText = 'Lower chances for loan approval';
                        else if (f.totalScore >= 80) f.approvalProbabilityText = 'High Likelihood';
                        else if (f.totalScore >= 70) f.approvalProbabilityText = 'Moderate Likelihood';
                        else f.approvalProbabilityText = 'Lower chances for loan approval';
                    }
                }
            ];

            document.getElementById('finalSubmitBtn').addEventListener('click', function(e) {
                e.preventDefault();

                const ncdValidationMessage = document.getElementById('ncdValidationMessage');

                // Check if step5 is visible
                if (!step5.classList.contains('step-hidden')) {
                    const ncdYes = document.getElementById('ncdYes');
                    const ncdNo = document.getElementById('ncdNo');
                    const ncdYesActive = ncdYes.classList.contains('bg-brandGold/15');
                    const ncdNoActive = ncdNo.classList.contains('bg-brandGold/15');
                    if (!ncdYesActive && !ncdNoActive) {
                        ncdValidationMessage.textContent = 'Please answer the NCD transfer question (Yes or No) before submitting.';
                        ncdValidationMessage.classList.remove('hidden');
                        return false;
                    }
                    if (ncdYesActive) {
                        const ncdPercentage = document.getElementById('ncdPercentage').value;
                        if (!ncdPercentage) {
                            ncdValidationMessage.textContent = 'Please select your current NCD percentage.';
                            ncdValidationMessage.classList.remove('hidden');
                            document.getElementById('ncdPercentage').focus();
                            return false;
                        }
                    }
                }

                ncdValidationMessage.classList.add('hidden');

                // Show a brief "AI is analyzing" loading animation before the score/result
                // modal appears, instead of revealing the result instantly on click.
                const submitBtn = document.getElementById('finalSubmitBtn');
                const overlay = document.getElementById('scoreLoadingOverlay');
                const statusEl = document.getElementById('scoreLoadingStatus');
                const loadingMessages = [
                    'Reading your financial details…',
                    'Checking credit & CCRIS records…',
                    'Calculating your readiness score…'
                ];

                submitBtn.disabled = true;
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');

                let messageIndex = 0;
                statusEl.textContent = loadingMessages[0];
                const messageInterval = setInterval(() => {
                    messageIndex = (messageIndex + 1) % loadingMessages.length;
                    statusEl.textContent = loadingMessages[messageIndex];
                }, 700);

                setTimeout(() => {
                    clearInterval(messageInterval);
                    overlay.classList.add('hidden');
                    overlay.classList.remove('flex');
                    submitBtn.disabled = false;
                    finalizeScoreSubmission();
                }, 2100);
            });

            function finalizeScoreSubmission() {
                // All validations passed. Gather raw facts from the form and let the
                // forward-chaining engine derive every sub-score, the total readiness
                // score, eligibility, and approval probability (see ELIGIBILITY_RULES
                // and runForwardChaining() above).
                // ===================================================================
                // SCORING RUBRIC — total 100 marks
                // 1. DSR (40) | 2. CCRIS (30) | 3. CTOS (15) | 4. Employment (5)
                // 5. Existing Loans (5) | 6. Insurance (2) | 7. NCD (3)
                // ===================================================================
                const salary = parseInt(document.getElementById('gaji').value) || 0;
                const depositVal = parseFloat(document.getElementById('deposit').value) || 0;
                const loanYears = parseInt(document.getElementById('loanYears').value);

                const dsrPercent = Math.round(calculateDSR() * 10) / 10;
                // Project the DSR after adding this new car loan: a new installment is capped at
                // ~18% of salary (see calculateAffordablePrice), so the projected DSR is
                // current DSR + 18 percentage points.
                const projectedDsrPercent = dsrPercent + 18;

                const facts = runForwardChaining({
                    dsrPercent,
                    ccrisMaxArrears: getCcrisMaxArrearsMonths(),
                    creditScoreValue: creditScoreInput.value,
                    jobSector: pekerjaanSelect.value,
                    jobType: document.getElementById('jobType').value,
                    loanCount: countActiveLoanCommitments(),
                    hasInsurance: document.getElementById('hasInsurance').checked,
                    ncdYesActive: document.getElementById('ncdYes').classList.contains('bg-brandGold/15'),
                    ncdPercentage: parseInt(document.getElementById('ncdPercentage').value, 10) || 0,
                    projectedDsrPercent
                }, ELIGIBILITY_RULES);

                const score = facts.totalScore;
                const dsrScore = facts.dsrScore;
                const ccrisScore = facts.ccrisScore;
                // PASS CONDITIONS: total score >= 70 AND DSR >= 20/40 AND CCRIS >= 20/30 (rule "eligibility")
                const passStatus = facts.passStatus;

                // Display results
                const arrearsSummary = getCcrisArrearsSummary();
                const creditStatus = getCreditStatusText(creditScoreInput.value, arrearsSummary);
                const maxMonthlyRepayment = Math.round(salary * 0.18);
                const maxVehiclePrice = calculateAffordablePrice(
                    salary,
                    depositVal,
                    loanYears,
                    parseFloat(document.getElementById('interestRate').value) || 2.5
                );
                const status = passStatus ? {
                    icon: '✓',
                    text: 'STATUS: PASSED (Eligible for Vehicle Recommendations)',
                    subtext: 'Your profile is strong and ready for recommendations.',
                    bannerClass: 'bg-emerald-50',
                    iconClass: 'bg-emerald-500',
                    textClass: 'text-emerald-900',
                    subtextClass: 'text-emerald-700/80'
                } : {
                    icon: '✕',
                    text: 'STATUS: FAILED (Not suitable to buy for now)',
                    subtext: 'Your score is low; improve your finances before buying a car.',
                    bannerClass: 'bg-rose-50',
                    iconClass: 'bg-rose-500',
                    textClass: 'text-rose-900',
                    subtextClass: 'text-rose-700/80'
                };

                document.getElementById('resultBox').classList.remove('hidden');
                // Banner: border radius now comes from the resultBox wrapper (overflow-hidden),
                // so we only need layout + status color classes here.
                document.getElementById('resultBanner').className =
                    `flex items-start gap-3 sm:gap-4 border-b border-slate-100 p-5 sm:p-6 ${status.bannerClass}`;
                document.getElementById('resultBannerIcon').innerText = status.icon;
                document.getElementById('resultBannerIcon').className =
                    `flex h-10 w-10 sm:h-11 sm:w-11 shrink-0 items-center justify-center rounded-full text-base sm:text-lg font-bold text-white ${status.iconClass}`;
                document.getElementById('resultBannerText').innerText = status.text;
                document.getElementById('resultBannerText').className =
                    `text-sm sm:text-base font-bold uppercase tracking-wide leading-snug ${status.textClass}`;
                document.getElementById('resultBannerSubtext').innerText = status.subtext;
                document.getElementById('resultBannerSubtext').className =
                    `mt-1 text-sm ${status.subtextClass}`;
                document.getElementById('dsrPercentValue').innerText = `${calculateDSR().toFixed(1)}%`;
                document.getElementById('dsrProgressBar').style.width = `${Math.min(Math.max(calculateDSR(), 0), 100)}%`;
                const creditStatusTextEl = document.getElementById('creditStatusText');
                creditStatusTextEl.innerText = creditStatus.text;
                creditStatusTextEl.className = `mt-1.5 text-xs font-semibold sm:mt-2 sm:text-sm ${creditStatus.tone === 'red' ? 'text-red-600' : creditStatus.tone === 'amber' ? 'text-amber-600' : 'text-emerald-600'}`;
                document.getElementById('approvalProbabilityText').innerText = facts.approvalProbabilityText;
                document.getElementById('readinessScoreValue').innerText = `${score} / 100`;
                document.getElementById('affordablePriceValue').innerText = formatCurrency(maxVehiclePrice);
                document.getElementById('monthlyCapValue').innerText = `${formatCurrency(maxMonthlyRepayment)} / month`;
                document.getElementById('affordabilitySection').classList.toggle('hidden', !passStatus);
                viewCarListBtn.href = buildCarListUrl();
                viewCarListBtn.classList.toggle('hidden', !passStatus);
                printReportBtn?.classList.toggle('hidden', !passStatus);

                // Show modal
                document.getElementById('resultBox').classList.remove('hidden');
                document.getElementById('resultBoxBackdrop').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            // Modal close functionality
            function closeResultModal() {
                document.getElementById('resultBox').classList.add('hidden');
                document.getElementById('resultBoxBackdrop').classList.add('hidden');
                document.body.style.overflow = '';
            }

            document.getElementById('resultBoxCloseBtn')?.addEventListener('click', closeResultModal);
            document.getElementById('resultBoxBackdrop')?.addEventListener('click', closeResultModal);

            function formatCurrency(value) {
                return `RM ${Number(value || 0).toLocaleString('en-MY', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
            }

            function getJobSectorLabel(value) {
                const map = {
                    gov: 'Government / GLC',
                    private: 'Private',
                    self: 'Free Lancer / Self Employed',
                    pesara: 'Pesara'
                };
                return map[value] || 'Not selected';
            }

            function getDsrScore(dsrPercent) {
                const safePercent = Number(dsrPercent || 0);
                return runForwardChaining({ dsrPercent: safePercent }, DSR_SCORE_RULES).dsrScore;
            }

            function buildReportData() {
                const dsrPercent = parseFloat(document.getElementById('dsrPercentValue')?.innerText || '0');
                const creditScoreSelect = document.getElementById('creditScore');
                const creditScoreText = creditScoreSelect?.options[creditScoreSelect.selectedIndex]?.text || 'Not selected';
                const ccrisSummary = getCcrisArrearsSummary();
                const ccrisText = ccrisSummary.hasSevereArrears ?
                    'Severe arrears detected in the CCRIS profile.' :
                    ccrisSummary.hasAnyArrears ?
                    'Arrears detected in the CCRIS profile.' :
                    'No arrears detected in the CCRIS profile.';
                const score = parseInt(document.getElementById('readinessScoreValue')?.innerText?.split('/')[0] || '0', 10);
                const dsrScore = getDsrScore(dsrPercent);
                const ccrisScore = ccrisSummary.hasSevereArrears ? 0 : ccrisSummary.hasAnyArrears ? 10 : 30;
                const ctosScore = creditScoreSelect?.value === 'excellent' ? 15 : creditScoreSelect?.value === 'good' ? 12 : creditScoreSelect?.value === 'fair' ? 8 : 0;
                const employmentScore = pekerjaanSelect.value === 'gov' ? 5 : pekerjaanSelect.value === 'private' && jobType.value === 'permanent' ? 4 : pekerjaanSelect.value === 'private' && jobType.value === 'contract' ? 2 : pekerjaanSelect.value === 'self' || pekerjaanSelect.value === 'pesara' ? 1 : 0;
                const insuranceScore = document.getElementById('hasInsurance').checked ? 2 : 0;
                const ncdScore = document.getElementById('ncdYes').classList.contains('bg-brandGold/15') ? 3 : 0;

                return {
                    name: document.getElementById('nama').value || 'N/A',
                    icNumber: document.getElementById('ic').value || 'N/A',
                    netSalary: document.getElementById('gaji').value || '0',
                    jobSector: getJobSectorLabel(pekerjaanSelect.value),
                    ctosScore: ctosScore,
                    ctosLabel: creditScoreText,
                    dsrPercent: `${Number(dsrPercent || 0).toFixed(1)}%`,
                    ccrisReport: ccrisText,
                    overallScore: score,
                    marks: [{
                            label: 'DSR',
                            score: dsrScore,
                            max: 40
                        },
                        {
                            label: 'CCRIS',
                            score: ccrisScore,
                            max: 30
                        },
                        {
                            label: 'CTOS',
                            score: ctosScore,
                            max: 15
                        },
                        {
                            label: 'Employment',
                            score: employmentScore,
                            max: 5
                        },
                        {
                            label: 'Insurance',
                            score: insuranceScore,
                            max: 2
                        },
                        {
                            label: 'NCD',
                            score: ncdScore,
                            max: 3
                        }
                    ]
                };
            }

            function generateReportPdf(reportData) {
                if (typeof window.jspdf === 'undefined') {
                    alert('PDF support is unavailable in this browser. Please use the browser print option.');
                    return;
                }

                const {
                    jsPDF
                } = window.jspdf;
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'pt',
                    format: 'a4'
                });
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                const margin = 50; // Standard professional margin
                let y = 0;

                // --- Elegant "DriveSmart" Diagonal Watermark Background ---
                pdf.saveGraphicsState();
                pdf.setTextColor(240, 243, 246); // Very faint grey/blue
                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(55);

                // Grid of watermarks
                for (let wmY = 150; wmY < pageHeight; wmY += 220) {
                    for (let wmX = -50; wmX < pageWidth; wmX += 250) {
                        pdf.text('DriveSmart', wmX, wmY, {
                            angle: 30
                        });
                    }
                }
                pdf.restoreGraphicsState();

                // --- Premium Header Band ---
                pdf.setFillColor(11, 37, 69); // Dark Executive Navy
                pdf.rect(0, 0, pageWidth, 110, 'F');

                // White/Silver Accent Line
                pdf.setFillColor(19, 64, 116); // Slate Blue accent line
                pdf.rect(0, 110, pageWidth, 4, 'F');

                // Header Typography
                pdf.setTextColor(255, 255, 255);
                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(20);
                pdf.text('DriveSmart Credit Assessment Report', margin, 45);

                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(10);
                pdf.setTextColor(218, 226, 236); // Light silver-blue text
                pdf.text('Confidential Official Bank-Grade Risk Evaluation Statement', margin, 68);
                pdf.text(`Prepared for: ${reportData.name}`, margin, 85);

                // Current Generation Timestamp (Standard Corporate Practice)
                const genDate = new Date().toLocaleDateString('en-MY', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                pdf.text(`Issued: ${genDate}`, pageWidth - margin, 85, {
                    align: 'right'
                });

                // --- Section: Applicant Details ---
                y = 150;
                pdf.setTextColor(11, 37, 69);
                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(12);
                pdf.text('APPLICANT PROFILE', margin, y);

                // Thin separating line
                y += 8;
                pdf.setDrawColor(19, 64, 116);
                pdf.setLineWidth(1);
                pdf.line(margin, y, pageWidth - margin, y);

                y += 20;
                pdf.setFontSize(10);
                const detailLines = [
                    [`Applicant Full Name`, reportData.name],
                    [`Identification (IC) Number`, reportData.icNumber],
                    [`Verified Net Salary`, `RM ${Number(reportData.netSalary || 0).toLocaleString('en-MY', { minimumFractionDigits: 2 })}`],
                    [`Employment Sector`, reportData.jobSector],
                    [`CTOS Credit Bureau Grade`, `${reportData.ctosLabel}`],
                    [`Debt Service Ratio (DSR)`, reportData.dsrPercent],
                    [`CCRIS Track Record Status`, reportData.ccrisReport]
                ];

                detailLines.forEach(([label, value]) => {
                    // Label column
                    pdf.setFont('helvetica', 'bold');
                    pdf.setTextColor(100, 116, 139); // Slate Grey
                    pdf.text(String(label), margin, y);

                    // Value column
                    pdf.setFont('helvetica', 'normal');
                    pdf.setTextColor(30, 41, 59); // Deep Slate
                    pdf.text(String(value), margin + 180, y);

                    // Light divider between rows
                    y += 6;
                    pdf.setDrawColor(241, 245, 249);
                    pdf.line(margin, y, pageWidth - margin, y);
                    y += 14;
                });

                // --- Section: Assessment Outcome Box ---
                y += 10;
                pdf.setTextColor(11, 37, 69);
                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(12);
                pdf.text('FINANCING EVALUATION OUTCOME', margin, y);

                y += 8;
                pdf.setDrawColor(19, 64, 116);
                pdf.line(margin, y, pageWidth - margin, y);

                y += 15;
                // Elegant Premium Outcome Box
                pdf.setDrawColor(203, 213, 225);
                pdf.setFillColor(248, 250, 252);
                pdf.roundedRect(margin, y, pageWidth - margin * 2, 65, 4, 4, 'FD');

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(14);
                pdf.setTextColor(11, 37, 69);
                pdf.text(`Overall Credit Rating Score: ${reportData.overallScore} / 100`, margin + 20, y + 26);

                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(9.5);
                pdf.setTextColor(71, 85, 105);
                pdf.text('This matrix is synthesized via computerized credit profiling criteria. It serves as an official framework for', margin + 20, y + 43);
                pdf.text('evaluating institutional vehicle financing readiness, auto-loan tier routing, and absolute default risk mitigation.', margin + 20, y + 54);

                // --- Section: Credit Risk Marks Breakdown Table ---
                y += 110;
                pdf.setTextColor(11, 37, 69);
                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(12);
                pdf.text('RISK COMPONENT BREAKDOWN', margin, y);

                y += 8;
                pdf.setDrawColor(19, 64, 116);
                pdf.line(margin, y, pageWidth - margin, y);

                // Table Header Row
                y += 20;
                pdf.setFillColor(226, 232, 240);
                pdf.rect(margin, y, pageWidth - margin * 2, 22, 'F');

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(9);
                pdf.setTextColor(71, 85, 105);
                pdf.text('ASSESSMENT CRITERIA / METRIC', margin + 15, y + 14);
                pdf.text('ALLOCATED SCORE', pageWidth - margin - 110, y + 14, {
                    align: 'right'
                });
                pdf.text('MAXIMUM WEIGHT', pageWidth - margin - 15, y + 14, {
                    align: 'right'
                });

                y += 22;

                // Table Data Rows
                reportData.marks.forEach((item, index) => {
                    // Alternating zebra backgrounds for clean reading
                    if (index % 2 === 1) {
                        pdf.setFillColor(248, 250, 252);
                        pdf.rect(margin, y, pageWidth - margin * 2, 22, 'F');
                    }

                    pdf.setFont('helvetica', 'bold');
                    pdf.setFontSize(9.5);
                    pdf.setTextColor(30, 41, 59);
                    pdf.text(item.label, margin + 15, y + 14);

                    pdf.setFont('helvetica', 'normal');
                    pdf.setTextColor(15, 23, 42);
                    pdf.text(`${item.score}`, pageWidth - margin - 110, y + 14, {
                        align: 'right'
                    });
                    pdf.text(`${item.max}`, pageWidth - margin - 15, y + 14, {
                        align: 'right'
                    });

                    // Row baseline separating metric from next row
                    pdf.setDrawColor(241, 245, 249);
                    pdf.line(margin, y + 22, pageWidth - margin, y + 22);

                    y += 22;
                });

                // --- Corporate Footer Disclaimer ---
                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(8);
                pdf.setTextColor(148, 163, 184);
                const disclaimerText = "Disclaimer: This document is generated securely by DriveSmart. The assessment values provided herein represent indicators based solely on standard algorithmic banking configurations. Final decisions are subject to discretionary underwriting policies.";
                const splitDisclaimer = pdf.splitTextToSize(disclaimerText, pageWidth - margin * 2);
                pdf.text(splitDisclaimer, margin, pageHeight - 45);

                // Save File execution
                pdf.save(`${(reportData.name || 'report').replace(/\s+/g, '_')}_driveSmart_report.pdf`);
            }

            function calculateAffordablePrice(salary, deposit, years, annualRate) {
                const netSalary = parseFloat(salary) || 0;
                const depositAmt = parseFloat(deposit) || 0;
                const loanYears = parseInt(years, 10) || 0;
                const rate = parseFloat(annualRate) || 2.5;

                if (netSalary <= 0 || loanYears <= 0) {
                    return 0;
                }

                const maxMonthlyPayment = netSalary * 0.18;
                const monthlyRate = rate / 100 / 12;
                const totalPayments = loanYears * 12;
                let maxLoanAmount;

                if (monthlyRate === 0) {
                    maxLoanAmount = maxMonthlyPayment * totalPayments;
                } else {
                    maxLoanAmount = maxMonthlyPayment * ((1 - Math.pow(1 + monthlyRate, -totalPayments)) / monthlyRate);
                }

                return Math.round(maxLoanAmount + depositAmt);
            }

            function getCcrisMaxArrearsMonths() {
                const inputs = document.querySelectorAll('#ccrisLoanSections input[data-loan-key]');
                let maxArrears = 0;
                inputs.forEach(input => {
                    const bucket = parseInt(input.getAttribute('data-arrears'), 10) || 0;
                    const value = parseInt(input.value, 10) || 0;
                    if (value > 0 && bucket > maxArrears) {
                        maxArrears = bucket;
                    }
                });
                return maxArrears;
            }

            function countActiveLoanCommitments() {
                // Single-instance commitments: counted as 1 loan each if active
                const singleLoanKeys = ['creditCard', 'asbFinancing', 'educationLoan', 'personalLoan'];
                // Multi-instance commitments: each non-empty installment row counts as a separate loan
                const multiLoanKeys = ['carLoan', 'motorcycleLoan', 'homeLoan'];
                let count = 0;

                singleLoanKeys.forEach(key => {
                    const yesBtn = document.querySelector(`.dsr-btn[data-loan="${key}"][data-value="yes"]`);
                    if (yesBtn?.classList.contains('bg-brandGold/15')) {
                        count += 1;
                    }
                });

                multiLoanKeys.forEach(key => {
                    const yesBtn = document.querySelector(`.dsr-btn[data-loan="${key}"][data-value="yes"]`);
                    if (yesBtn?.classList.contains('bg-brandGold/15')) {
                        const rows = getDsrInstallmentValues(key).filter(v => v > 0).length;
                        count += Math.max(rows, 1);
                    }
                });

                return count;
            }

            function getCcrisArrearsSummary() {
                const inputs = document.querySelectorAll('#ccrisLoanSections input[data-loan-key]');
                let hasAnyArrears = false;
                let hasSevereArrears = false;

                inputs.forEach(input => {
                    const monthsInArrears = parseInt(input.getAttribute('data-arrears'), 10) || 0;
                    const value = parseInt(input.value, 10) || 0;

                    if (monthsInArrears > 0 && value > 0) {
                        hasAnyArrears = true;
                    }
                    if (monthsInArrears > 3 && value > 0) {
                        hasSevereArrears = true;
                    }
                });

                return {
                    hasAnyArrears,
                    hasSevereArrears
                };
            }

            function getCreditStatusText(value, arrearsSummary = null) {
                if (arrearsSummary?.hasSevereArrears) {
                    return {
                        text: 'Severe Arrears Detected',
                        tone: 'red'
                    };
                }
                if (arrearsSummary?.hasAnyArrears) {
                    return {
                        text: 'Arrears Detected',
                        tone: 'amber'
                    };
                }
                if (value === 'excellent' || value === 'good') {
                    return {
                        text: 'Clean / No Arrears',
                        tone: 'emerald'
                    };
                }
                if (value === 'fair') {
                    return {
                        text: 'Some concerns / review required',
                        tone: 'amber'
                    };
                }
                return {
                    text: 'Risk of arrears / needs improvement',
                    tone: 'red'
                };
            }

            // DSR Button Logic (for all debt questions)
            document.querySelectorAll('.dsr-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const loan = this.getAttribute('data-loan');
                    const value = this.getAttribute('data-value');
                    document.querySelectorAll(`.dsr-btn[data-loan="${loan}"]`).forEach(b => b.classList.remove('bg-brandGold/15', 'border-brandGold'));
                    this.classList.add('bg-brandGold/15', 'border-brandGold');
                    const div = document.getElementById(`${loan}InstallmentDiv`);
                    if (value === 'yes') {
                        div?.classList.remove('hidden');
                        if (['carLoan', 'motorcycleLoan', 'homeLoan'].includes(loan)) {
                            const list = document.getElementById(`${loan}InstallmentList`);
                            if (list && list.children.length === 0) {
                                list.appendChild(createDsrInstallmentRow(loan));
                            }
                        }
                    } else {
                        div?.classList.add('hidden');
                        clearDsrInstallmentRows(loan);
                    }
                    calculateDSR();
                });
            });

            document.addEventListener('input', function(event) {
                if (event.target instanceof HTMLElement && event.target.classList.contains('dsr-installment-input')) {
                    calculateDSR();
                }
                if (event.target instanceof HTMLElement && event.target.matches('#ccrisLoanSections input')) {
                    validateCcrisEntries();
                }
            });

            document.getElementById('gaji')?.addEventListener('input', calculateDSR);

            // Logic for the NCD transfer question
            function toggleNCDDetails(isYes) {
                const dropdown = document.getElementById('ncdDropdown');
                const ncdYes = document.getElementById('ncdYes');
                const ncdNo = document.getElementById('ncdNo');

                if (isYes) {
                    dropdown.classList.remove('hidden');
                    ncdYes.classList.add('bg-brandGold/15', 'border-brandGold');
                    ncdNo.classList.remove('bg-brandGold/15', 'border-brandGold');
                } else {
                    dropdown.classList.add('hidden');
                    ncdNo.classList.add('bg-brandGold/15', 'border-brandGold');
                    ncdYes.classList.remove('bg-brandGold/15', 'border-brandGold');
                }

                updateFinalSubmitVisibility();
            }

            // The Submit button stays hidden until the NCD question is fully answered:
            // choosing "No" reveals it immediately; choosing "Yes" also requires a
            // percentage to be picked from the dropdown first.
            function updateFinalSubmitVisibility() {
                const ncdYes = document.getElementById('ncdYes');
                const ncdNo = document.getElementById('ncdNo');
                const ncdPercentage = document.getElementById('ncdPercentage');
                const finalSubmitBtn = document.getElementById('finalSubmitBtn');

                const ncdYesActive = ncdYes.classList.contains('bg-brandGold/15');
                const ncdNoActive = ncdNo.classList.contains('bg-brandGold/15');
                const ready = ncdNoActive || (ncdYesActive && ncdPercentage.value !== '');

                finalSubmitBtn.classList.toggle('hidden', !ready);
            }

            document.getElementById('ncdPercentage')?.addEventListener('change', updateFinalSubmitVisibility);

            const nameInput = document.getElementById('nama');
            nameInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.toUpperCase();
            });

            // 1. IC Formatting & Age Calculation
            icInput.addEventListener('input', function() {
                let val = this.value.replace(/\D/g, '');
                let formatted = "";
                if (val.length > 0) {
                    formatted += val.substring(0, 6);
                    if (val.length >= 7) formatted += "-" + val.substring(6, 8);
                    if (val.length >= 9) formatted += "-" + val.substring(8, 12);
                }
                this.value = formatted;
                if (val.length >= 2) {
                    const yearPart = parseInt(val.substring(0, 2));
                    const currentYear = 2026;
                    let birthYear = (yearPart <= 26) ? (2000 + yearPart) : (1900 + yearPart);
                    ageInput.value = currentYear - birthYear;
                }
                validatePesaraAge();
            });

            // 2. Existing Sector & Validation logic
            pekerjaanSelect.addEventListener('change', function() {
                const val = this.value;
                document.getElementById('privateAccordion').className = (val === 'private') ? 'accordion-content bg-blue-50 rounded-lg accordion-open' : 'accordion-content bg-blue-50 rounded-lg';
                if (val === 'pesara' || val === 'self') {
                    jobType.disabled = true;
                    jobType.classList.add('dimmed');
                    jobType.value = "";
                } else {
                    jobType.disabled = false;
                    jobType.classList.remove('dimmed');
                }
                validatePesaraAge();
            });

            // 3. Pesara Age Validation
            function validatePesaraAge() {
                const age = parseInt(ageInput.value);
                const isPesara = pekerjaanSelect.value === 'pesara';
                const warning = document.getElementById('pesaraWarning');
                const nextBtn = document.getElementById('step1NextBtn');
                const shouldDisable = isPesara && age >= 70;

                if (shouldDisable) {
                    warning.classList.remove('hidden');
                    if (nextBtn) {
                        nextBtn.disabled = true;
                        nextBtn.classList.add('dimmed');
                    }
                } else {
                    warning.classList.add('hidden');
                    if (nextBtn) {
                        nextBtn.disabled = false;
                        nextBtn.classList.remove('dimmed');
                    }
                }
            }

            // CCRIS / CTOS Credit Score Labeling (Real-time)
            const creditScoreInput = document.getElementById("creditScore");
            const scoreLabel = document.getElementById("scoreLabel");
            const interestRateInput = document.getElementById('interestRate');
            const interestValueDisplay = document.getElementById('interestValue');
            const viewCarListBtn = document.getElementById('viewCarListBtn');

            function buildCarListUrl() {
                const salary = document.getElementById('gaji').value || 0;
                const deposit = document.getElementById('deposit').value || 0;
                const loanYears = document.getElementById('loanYears').value || 0;
                const interest = document.getElementById('interestRate').value || 2.5;
                const params = new URLSearchParams();
                params.set('gaji', salary);
                params.set('deposit', deposit);
                params.set('loanYears', loanYears);
                params.set('interest', interest);
                return `cars.php?${params.toString()}`;
            }

            interestRateInput.addEventListener('input', function() {
                interestValueDisplay.innerText = `${parseFloat(this.value).toFixed(1)}%`;
            });

            creditScoreInput.addEventListener('change', function() {
                const val = this.value;

                if (val === "excellent") {
                    scoreLabel.innerText = "Excellent credit score ";
                    scoreLabel.className = "text-xs mt-2 font-bold text-green-600";
                } else if (val === "good") {
                    scoreLabel.innerText = "Good credit score";
                    scoreLabel.className = "text-xs mt-2 font-bold text-blue-600";
                } else if (val === "fair") {
                    scoreLabel.innerText = "Fair credit score";
                    scoreLabel.className = "text-xs mt-2 font-bold text-yellow-600";
                } else if (val === "poor") {
                    scoreLabel.innerText = "Poor credit score";
                    scoreLabel.className = "text-xs mt-2 font-bold text-red-600";
                } else {
                    scoreLabel.innerText = "";
                }
            });
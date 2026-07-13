// main.js
document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('mobile-menu-button');
    const menu = document.getElementById('mobile-menu');

    if (button && menu) {
        button.addEventListener('click', () => {
            const isExpanded = menu.classList.toggle('hidden');
            button.setAttribute('aria-expanded', String(!isExpanded));
        });
    }

    // Attach calculator listeners only if the calculator exists on this page
    const calculateButton = document.getElementById('calculateButton');
    const netSalaryInput = document.getElementById('netSalary');
    const depositInput = document.getElementById('deposit');
    const loanYearsInput = document.getElementById('loanYears');
    const interestRateInput = document.getElementById('interestRate');

    if (calculateButton) calculateButton.addEventListener('click', calculateMaxCarPrice);
    if (netSalaryInput) netSalaryInput.addEventListener('input', calculateMaxCarPrice);
    if (depositInput) depositInput.addEventListener('input', calculateMaxCarPrice);
    if (loanYearsInput) loanYearsInput.addEventListener('change', calculateMaxCarPrice);
    if (interestRateInput) interestRateInput.addEventListener('input', calculateMaxCarPrice);
});

// Calculator logic
function calculateMaxCarPrice() {
    const netSalary = parseFloat(document.getElementById('netSalary').value);
    const deposit = parseFloat(document.getElementById('deposit').value);
    const loanYears = parseInt(document.getElementById('loanYears').value);
    const annualRate = parseFloat(document.getElementById('interestRate').value);

    const carPriceEl = document.getElementById('suitableCarPrice');
    const maxLoanEl = document.getElementById('maxLoanAmountDisplay');
    const monthlyPmtResultEl = document.getElementById('monthlyPaymentResult');
    const displayMaxPriceEl = document.getElementById('displayMaxPrice');

    carPriceEl.textContent = 'RM 0';
    maxLoanEl.textContent = 'Max Loan Amount Financed: RM 0';
    monthlyPmtResultEl.textContent = 'RM 0';
    if (displayMaxPriceEl) displayMaxPriceEl.textContent = 'RM 0';

    if (isNaN(netSalary) || netSalary <= 0 || isNaN(deposit) || deposit < 0 || isNaN(annualRate) || annualRate < 0 || isNaN(loanYears) || loanYears <= 0) {
        carPriceEl.textContent = 'Invalid Input';
        if (displayMaxPriceEl) displayMaxPriceEl.textContent = 'Invalid Input';
        return;
    }

    // Shared amortized loan formula: use 18% of net salary as the max monthly payment
    const maxMonthlyPayment = netSalary * 0.18;
    const monthlyRate = annualRate / 100 / 12;
    const totalPayments = loanYears * 12;
    let maxLoanAmount;

    if (monthlyRate === 0) {
        maxLoanAmount = maxMonthlyPayment * totalPayments;
    } else {
        const amortizationFactor = (1 - Math.pow(1 + monthlyRate, -totalPayments)) / monthlyRate;
        maxLoanAmount = maxMonthlyPayment * amortizationFactor;
    }

    const maxCarPrice = maxLoanAmount + deposit;

    const formatCurrency = (value) => 'RM ' + value.toLocaleString('en-US', { maximumFractionDigits: 0 });

    carPriceEl.textContent = formatCurrency(maxCarPrice);
    if (displayMaxPriceEl) displayMaxPriceEl.textContent = formatCurrency(maxCarPrice);
    maxLoanEl.textContent = `Max Loan Amount Financed: RM ${maxLoanAmount.toLocaleString('en-US', { maximumFractionDigits: 0 })}`;
    monthlyPmtResultEl.textContent = formatCurrency(maxMonthlyPayment);
}

//About page
//image slider
document.addEventListener('DOMContentLoaded', () => {
    const slider = document.getElementById('slider');
    const dots = document.querySelectorAll('.dot');
    let currentIndex = 0;
    const totalSlides = 5;

    function updateSlider() {
        // This moves the slider by -100%, -200%, etc.
        slider.style.transform = `translateX(-${currentIndex * 100}%)`;
        
        // This updates the dots' appearance
        dots.forEach((dot, index) => {
            if (index === currentIndex) {
                dot.classList.add('bg-white');
                dot.classList.remove('bg-white/50');
            } else {
                dot.classList.remove('bg-white');
                dot.classList.add('bg-white/50');
            }
        });
    }

    function nextSlide() {
        currentIndex = (currentIndex + 1) % totalSlides;
        updateSlider();
    }

    // This makes it move every 3 seconds (3000ms)
    setInterval(nextSlide, 3000);
});
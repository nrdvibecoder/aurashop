window.deliveryZones = window.deliveryZones || [];

document.addEventListener('DOMContentLoaded', () => {
    const wilayaSelect = document.getElementById('wilaya-select');
    const deliveryMethodHome = document.getElementById('delivery-home');
    const deliveryMethodRelay = document.getElementById('delivery-relay');
    const deliveryFeeDisplay = document.getElementById('delivery-fee-display');
    const deliveryFeeInput = document.getElementById('delivery-fee-input');
    const deliveryDaysDisplay = document.getElementById('delivery-days-display');

    function updateDeliveryFee() {
        if (!wilayaSelect) return;
        const selectedWilaya = wilayaSelect.value;
        const zone = window.deliveryZones.find(z => z.wilaya_name === selectedWilaya);

        if (!zone) {
            if (deliveryFeeDisplay) deliveryFeeDisplay.textContent = '-- DA';
            if (deliveryFeeInput) deliveryFeeInput.value = 0;
            return;
        }

        const isRelay = deliveryMethodRelay && deliveryMethodRelay.checked;
        const fee = isRelay ? parseInt(zone.relay_fee) : parseInt(zone.home_fee);
        const days = zone.estimated_days;

        if (deliveryFeeDisplay) deliveryFeeDisplay.textContent = formatDA(fee);
        if (deliveryFeeInput) deliveryFeeInput.value = fee;
        if (deliveryDaysDisplay) deliveryDaysDisplay.textContent = `${days} jour${days > 1 ? 's' : ''} / day${days > 1 ? 's' : ''}`;

        
        updateOrderTotal();
    }

    function updateOrderTotal() {
        const subtotalEl = document.getElementById('summary-subtotal');
        const feeEl = document.getElementById('summary-delivery-fee');
        const discountEl = document.getElementById('summary-discount');
        const totalEl = document.getElementById('summary-total');

        if (!subtotalEl || !totalEl) return;

        const subtotal = parseInt(subtotalEl.dataset.amount || 0);
        const fee = parseInt(deliveryFeeInput ? deliveryFeeInput.value : 0);
        const discount = parseInt(discountEl ? (discountEl.dataset.amount || 0) : 0);
        const total = subtotal + fee - discount;

        if (feeEl) feeEl.textContent = formatDA(fee);
        if (totalEl) totalEl.textContent = formatDA(Math.max(0, total));
        totalEl.dataset.amount = Math.max(0, total);

        
        const totalInput = document.getElementById('final-total-input');
        if (totalInput) totalInput.value = Math.max(0, total);
    }

    if (wilayaSelect) {
        wilayaSelect.addEventListener('change', updateDeliveryFee);
    }
    if (deliveryMethodHome) {
        deliveryMethodHome.addEventListener('change', updateDeliveryFee);
    }
    if (deliveryMethodRelay) {
        deliveryMethodRelay.addEventListener('change', updateDeliveryFee);
    }

    
    const steps = document.querySelectorAll('[data-step]');
    const stepIndicators = document.querySelectorAll('[data-step-indicator]');
    let currentStep = 1;

    function showStep(stepNum) {
        steps.forEach(s => {
            const stepN = parseInt(s.dataset.step);
            s.style.display = (stepN === stepNum) ? 'block' : 'none';
        });
        stepIndicators.forEach(ind => {
            const indN = parseInt(ind.dataset.stepIndicator);
            ind.classList.toggle('bg-primary', indN <= stepNum);
            ind.classList.toggle('text-on-primary', indN <= stepNum);
            ind.classList.toggle('border-primary', indN === stepNum);
            ind.classList.toggle('border-outline-variant/30', indN > stepNum);
            ind.classList.toggle('text-on-surface-variant', indN > stepNum);
        });
        currentStep = stepNum;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    const nextBtns = document.querySelectorAll('[data-step-next]');
    const backBtns = document.querySelectorAll('[data-step-back]');

    nextBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const nextStep = parseInt(btn.dataset.stepNext);
            if (currentStep === 1 && !validateStep1()) return;
            showStep(nextStep);
            if (nextStep === 2) updateDeliveryFee();
        });
    });

    backBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const prevStep = parseInt(btn.dataset.stepBack);
            showStep(prevStep);
        });
    });

    
    if (steps.length > 0) showStep(1);

    
    function validateStep1() {
        const fullname = document.getElementById('input-fullname');
        const phone = document.getElementById('input-phone');
        const wilaya = document.getElementById('wilaya-select');
        const commune = document.getElementById('input-commune');
        const address = document.getElementById('input-address');

        let valid = true;
        [fullname, phone, wilaya, commune, address].forEach(field => {
            if (field && !field.value.trim()) {
                field.classList.add('border-error');
                valid = false;
            } else if (field) {
                field.classList.remove('border-error');
            }
        });

        
        if (phone && phone.value && !/^(05|06|07)\d{8}$/.test(phone.value.replace(/\s/g, ''))) {
            phone.classList.add('border-error');
            showToast('Format téléphone invalide (ex: 0612345678)');
            valid = false;
        }

        if (!valid) showToast('Veuillez remplir tous les champs requis.');
        return valid;
    }

    
    updateDeliveryFee();
});

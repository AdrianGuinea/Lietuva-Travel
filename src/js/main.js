function submitForm() {
    try { sessionStorage.setItem('lt-scroll', String(window.scrollY)); } catch (e) {}
    document.body.classList.add('is-loading');
    document.getElementById('builderForm').submit();
}

function selectRadio(name, val) {
    document.querySelectorAll(`input[name="${name}"]`).forEach(r => r.checked = (r.value === val));
    submitForm();
}

function selectCity(c) {
    document.getElementById('cityInput').value = c;
    document.querySelectorAll('input[name="flight_id"]').forEach(r => r.checked = (r.value === 'AUTO'));
    document.querySelectorAll('input[name="hotel_id"]').forEach(r => r.checked = (r.value === 'AUTO'));
    submitForm();
}

function changeLanguage(lang) {
    const langInput = document.getElementById('langInput');
    if (langInput) {
        langInput.value = lang;
        submitForm();
    }
}

function loadScenario(city, origin, flightId, hotelId, activityId, weatherMode, budget) {
    const form = document.getElementById('builderForm');
    document.getElementById('cityInput').value = city;
    form.querySelector('select[name="origin_airport"]').value = origin;
    form.querySelector('select[name="weather_mode"]').value = weatherMode;
    form.querySelector('input[name="budget"]').value = budget;

    const addHidden = (name, val) => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = name;
        inp.value = val;
        form.appendChild(inp);
    };

    addHidden('flight_id', flightId);
    addHidden('hotel_id', hotelId);

    document.querySelectorAll('input[name="activity_ids[]"]').forEach(cb => cb.checked = false);
    if (activityId) {
        addHidden('activity_ids[]', activityId);
    }

    submitForm();
}

document.addEventListener('DOMContentLoaded', () => {
    try {
        const y = sessionStorage.getItem('lt-scroll');
        if (y !== null) {
            window.scrollTo({ top: parseInt(y, 10) || 0, behavior: 'instant' });
            sessionStorage.removeItem('lt-scroll');
        }
    } catch (e) {}

    const slider = document.querySelector('input[name="budget"]');
    const label = document.getElementById('budgetValue');
    if (slider && label) {
        const rate = parseFloat(slider.dataset.rate) || 1;
        const symbol = slider.dataset.symbol || '€';
        slider.addEventListener('input', () => {
            label.textContent = symbol + (slider.value * rate).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        });
    }
});

window.addEventListener('pageshow', e => {
    if (e.persisted) document.body.classList.remove('is-loading');
});

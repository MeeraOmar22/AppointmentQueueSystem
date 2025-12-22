<h2>Chatbot Appointment Booking</h2>

<div id="chat-box"></div>

<input type="text" id="input" placeholder="Type here..." />
<button onclick="send()">Send</button>

<script>
let step = 'name';

const questions = {
    name: "Hi! May I have your name?",
    phone: "Thanks! Please enter your phone number.",
    service: "What service would you like? (e.g. dental checkup, tooth extraction)",
    dentist: "Any preferred dentist? (type 1 if none)",
    date: "When would you like the appointment? (today / tomorrow / YYYY-MM-DD)",
    time: "What time suits you? (now / morning / afternoon / HH:MM)",
    confirm: "Type CONFIRM to finalize your appointment"
};


document.getElementById('chat-box').innerHTML +=
    `<p><strong>Bot:</strong> ${questions.name}</p>`;

function send() {
    const value = document.getElementById('input').value;
    document.getElementById('input').value = '';

    document.getElementById('chat-box').innerHTML +=
        `<p><strong>You:</strong> ${value}</p>`;

    fetch('/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ value })
    })
    .then(res => res.json())
    .then(data => {
        if (data.message) {
            document.getElementById('chat-box').innerHTML +=
                `<p><strong>Bot:</strong> ${data.message}</p>`;
        }

        if (!data.done) {
            step = data.next;
            document.getElementById('chat-box').innerHTML +=
                `<p><strong>Bot:</strong> ${questions[step]}</p>`;
        }
    });
}
</script>

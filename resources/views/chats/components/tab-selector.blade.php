<div class="tab-selector d-flex">
	<button type="button" class="tab-btn active" data-tab="private">Личные чаты</button>
	<button type="button" class="tab-btn" data-tab="group">Групповые чаты</button>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
	const tabs = document.querySelectorAll('.tab-btn');
	tabs.forEach(tab => {
		tab.addEventListener('click', function(){
			tabs.forEach(t => t.classList.remove('active'));
			this.classList.add('active');
			// Сообщаем о смене вкладки
			const event = new CustomEvent('chatTabChanged', { detail: this.getAttribute('data-tab') });
			document.dispatchEvent(event);
		});
	});
});
</script>

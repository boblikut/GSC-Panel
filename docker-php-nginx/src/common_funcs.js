let noticingTimerId;
function notice(str, fade){
	const notification = document.getElementById('notification');
	
	notification.textContent = str;
	clearTimeout(noticingTimerId);
	notification.style.transition = 'opacity 0s ease, right 0s ease';
	notification.classList.remove('show');
	void notification.offsetWidth;
	notification.style.transition = 'opacity 0.25s ease, right 0.25s ease';
	notification.classList.add('show');
	
	noticingTimerId = setTimeout(() => {
		notification.classList.remove('show');
	}, fade);
}
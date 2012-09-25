function mo(ref) {
	a = document.getElementById('disp');
	c = document.getElementById('disptx');
	a.style.backgroundColor = ref.style.backgroundColor;
	c.innerHTML = "Hex: " + ref.innerHTML; // + " " + ref.style.backgroundColor; 
}

function cl(ref) {
	b = document.getElementById('selc');
	d = document.getElementById('selctx');
	b.style.backgroundColor = ref.style.backgroundColor;
	d.innerHTML = "Hex: " + ref.innerHTML; // + " " + ref.style.backgroundColor; 
}
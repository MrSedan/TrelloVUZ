window.onload = ()=>{
  const button = document.querySelector(".btn");
  const counter = document.querySelector("#counter");

  button.addEventListener('click', ()=>{
    button.classList.add('animated');
    counter.textContent = parseInt(counter.textContent,10)+1;

    setTimeout(()=>{
      button.classList.remove('animated');
    },500);
  });
}
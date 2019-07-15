
function courseDetail(course_id) {
    let div = document.getElementById(course_id);
    div.classList.add ("block");
}
function close_course(element) {
    let parent = element.parentNode;
    parent.classList.remove("block");
}

function no_authorize(element) {
    let dataText = element.getAttribute("data-text");
    alert(dataText);
}

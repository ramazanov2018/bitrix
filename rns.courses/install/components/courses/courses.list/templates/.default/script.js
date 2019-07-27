function Courses() {

    Courses.getXmlHttp = function () {
        if (window.XMLHttpRequest) {
            return new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            try {
                return new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                try {
                    return new ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {
                    return false;
                }
            }
        }
    };

    this.on = function(elSelector, eventName, selector, fn) {
        let element = document.querySelector(elSelector);

        element.addEventListener(eventName, function(event) {
            let possibleTargets = element.querySelectorAll(selector);
            let target = event.target;

            for (let i = 0, l = possibleTargets.length; i < l; i++) {
                let el = target;
                let p = possibleTargets[i];

                while(el && el !== element) {
                    if (el === p) {
                        return fn.call(p, event);
                    }

                    el = el.parentNode;
                }
            }
        });
    };

    this.addMember = function (e) {
        let btn =  e.target;

        let url = btn.getAttribute("data-url");
        let courseID = btn.getAttribute("data-course");
        let data = JSON.stringify({course: "addMember", courseID: courseID});

        let xmlHttp = Courses.getXmlHttp();
        xmlHttp.onreadystatechange = function () {
            if (xmlHttp.readyState === 4 && xmlHttp.status === 200) {
                let result = JSON.parse(xmlHttp.responseText);
                if (result.status === "success")
                {
                    btn.classList.remove('btn-primary');
                    btn.classList.add("btn-default");
                    btn.setAttribute('data-role','removeMember');
                    btn.text = "Отказаться";
                }else
                {
                    alert(result.error);
                }
            }
        };

        xmlHttp.open("POST", url, true);
        xmlHttp.send(data);
    };

    this.removeMember = function (e) {
        let btn =  e.target;

        let url = btn.getAttribute("data-url");
        let courseID = btn.getAttribute("data-course");
        let data = JSON.stringify({course: "removeMember", courseID: courseID});

        let xmlHttp = Courses.getXmlHttp();

        xmlHttp.onreadystatechange = function () {
            if (xmlHttp.readyState === 4 && xmlHttp.status === 200) {
                let result = JSON.parse(xmlHttp.responseText);
                if (result.status === "success")
                {
                    btn.classList.remove('btn-default');
                    btn.classList.add("btn-primary");
                    btn.setAttribute('data-role','addMember');
                    btn.text = "Подписаться";
                }else
                {
                    alert(result.error);
                }
            }
        };

        xmlHttp.open("POST", url, true);
        xmlHttp.send(data);
    };

    this.init = function () {
        this.on('body', 'click', 'a[data-role=\"addMember\"]', this.addMember);
        this.on('body', 'click', 'a[data-role=\"removeMember\"]', this.removeMember);
    };

    this.run = function () {
        this.init();
    };
}

function courseDetail(course_id) {
    let div = document.getElementById(course_id);
    div.classList.add ("block");
}
function close_course(element) {
    let parent = element.parentNode;
    parent.classList.remove("block");
}

window.onload = function () {
    let Course = new Courses();
    Course.run();
};
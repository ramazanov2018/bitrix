BX.ready(function () {

    /*
     * Добавляем пункт "Дни рождения" меню в профиле ползователя
     * */
    BX.addCustomEvent("BX.UI.EntityConfigurationManager:onInitialize", BX.delegate((editor, settings) => {

        if (editor.getId() !== "intranet-user-profile") {
            return;
        }

        let topMenuId = "#socialnetwork_profile_menu_user_" + editor._entityId;
        let topMenuNode = document.querySelector(topMenuId);

        if (!BX.type.isDomNode(topMenuNode)) {
            return;
        }

        let item = BX.create("div", {
            attrs: {
                className: "main-buttons-item",
                id: "socialnetwork_profile_menu_user_" + editor._entityId + "_learning",
                draggable: true,
                tabindex: -1,
            },
            dataset: {
                disabled: false,
                id: "learning",
                topMenuId: topMenuId,
            },
        });

        item.innerHTML = '<span class="main-buttons-item-link">' +
            '<span class="main-buttons-item-text-title">' +
            '<span class="main-buttons-item-text-box">Дни рождения</span>' +
            '</span>' +
            '</span>';

        item.onclick = function (event) {openSidePanel("/birthdays/")}

        // Добавим его вторым
        BX.insertAfter(item, topMenuNode.firstElementChild);
    }));

    /*
     * Добавляем пункт "Дни рождения" в меню Задачи/детальная задача/еще
     * */
    BX.addCustomEvent("onPopupFirstShow",  BX.delegate((p) => {
        var menuId = 'task-view-b';
        if (p.uniquePopupId === 'menu-popup-' + menuId)
        {
            var menu = BX.PopupMenu.getMenuById(menuId),
                href = '/birthdays/';

            menu.addMenuItem({
                text: 'Дни рождения',
                href: href,
                className: 'menu-popup-item'
            });
        }
    }));
});


function openSidePanel(url) {
    BX.SidePanel.Instance.open(url, {
        cacheable: false,
    });
}

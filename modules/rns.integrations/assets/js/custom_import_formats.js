BX.ready(function() {

  BX.addCustomEvent('onPopupFirstShow', function(p) {
    var text = 'Импорт списка';
    if (p.uniquePopupId === 'popupMenuOptions') {
      var items = p.params.items;
      var menuItem = items.find(x => x.text === text);
      if (!menuItem) return;
      menuItem.items.push({
        tabId: 'popupMenuOptions',
        text: 'из MPP',
        href: '/company/personal/user/' + BX.message('USER_ID') + '/tasks/import/?format=mpp',
        className: 'tasks-interface-filter-icon-project'
      });
    }
  });
});

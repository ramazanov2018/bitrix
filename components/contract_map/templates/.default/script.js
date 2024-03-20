"use strict";

if (typeof(BX.ContractMap) === "undefined")
{
    BX.ContractMap = function ()
    {
        this._params = {};

    };

    BX.ContractMap.prototype =
        {
            initialize: function (params) {
                var self = this
                self._params = params ? params : {};
                self._params.coordsObject = self._params.coordsObject ? self._params.coordsObject : {};
                self._params.urlJsonPolygon = self._params.urlJsonPolygon ? self._params.urlJsonPolygon : {};
                self._params.urlJsonPolygon2 = self._params.urlJsonPolygon2 ? self._params.urlJsonPolygon2 : {};
                $(function () {
                    var $areaSelect = $('select[name="area"]');
                    $areaSelect.select2({
                        placeholder: "Регион",
                        language: {
                            noResults: function noResults() {
                                return "Нет результатов";
                            }
                        }
                    });
                    var $checkbox = $('.area-filter__wrapper-checkbox .checkboxInput input:checkbox'),
                        $switchCheck = $('#areaObject .switch__checkbox'),
                        $wrapperCheckbox = $('.area-filter__wrapper-checkbox'),
                        $legend = $('.area-map-legend');
                    var areaId = 'area-0',
                        mapArea = {
                            map: '',
                            objectManager: '',
                            areaData: '',
                            polygonManager: ''
                        };
                    var areaDataMap = {
                        mapId: 'area-map',
                        coordsObject: self._params.coordsObject,
                        mapCenter: [59.9386, 30.3141],
                        mapZoom: 10,
                        //urlJsonPolygon: "/local/components/rns/contract_map\\templates\\.default\\mapCoordinate.json"
                        jsonPolygon: self._params.urlJsonPolygon
                    };
                    var setFilter = {
                        area: '',
                        array: []
                    };
                    ymaps.ready(function () {
                        mapArea = myMap({
                            data: areaDataMap,
                            filter: setFilter
                        });
                    });
                    $areaSelect.on('select2:select', function (e) {
                        areaId = e.params.data.element.id;
                        if (areaId !== 'area-0') {
                            $('.area-switch').fadeIn("fade");
                            setFilter.areaId = areaId;
                            mapFilter(setFilter, mapArea.objectManager);
                            var mapCenter = findAreaCoordinate(areaId, mapArea.areaData);
                            mapSelectedArea(mapArea.map, mapCenter);
                        } else {
                            $legend.fadeOut("fade");
                            $wrapperCheckbox.fadeOut("fade");
                            $('.area-switch').fadeOut("fade");
                            $('.area-switch .switch__checkbox').prop('checked', false);
                            mapSelectedArea(mapArea.map, areaDataMap.mapCenter, areaDataMap.mapZoom, {
                                polygonManager: mapArea.polygonManager,
                                polygonArray: mapArea.polygonArray
                            });
                            setFilter.array = [];
                            mapFilter(setFilter, mapArea.objectManager);
                        }
                    });
                    $switchCheck.on('change', function () {
                        if (!$switchCheck.prop('checked')) {
                            $wrapperCheckbox.fadeOut("fade");
                            setFilter.array = [];
                            mapFilter(setFilter, mapArea.objectManager);
                            setTimeout(function () {
                                $checkbox.each(function (i, item) {
                                    if (!$(item).prop('checked')) {
                                        $(item).prop('checked', true);
                                    }
                                });
                            }, 500);
                            $legend.fadeOut("fade");
                        } else {
                            $wrapperCheckbox.fadeIn("fade");
                            $legend.fadeIn("fade");
                            $checkbox.each(function (i, item) {
                                setFilter.array.push($(item).prop('name'));
                            });
                            mapFilter(setFilter, mapArea.objectManager);
                        }
                    });
                    $checkbox.each(function (i, item) {
                        $(item).on('click', function () {
                            if ($(item).prop('checked')) {
                                setFilter.array.push($(item).prop('name'));
                                mapFilter(setFilter, mapArea.objectManager);
                            } else {
                                var newArray = [];
                                setFilter.array.forEach(function (i) {
                                    if (i !== $(item).prop('name')) {
                                        newArray.push(i);
                                    }
                                });
                                setFilter.array = newArray;
                                mapFilter(setFilter, mapArea.objectManager);
                            }
                        });
                    });
                    $('.area-filter__wrapper-checkbox .show-all').click(function () {
                        var newArray = [];
                        $checkbox.each(function (i, item) {
                            if (!$(item).prop('checked')) {
                                $(item).prop('checked', true);
                            }
                            newArray.push($(item).prop('name'));
                        });
                        setFilter.array = newArray;
                        mapFilter(setFilter, mapArea.objectManager);
                    });
                    $('.area-filter__wrapper-checkbox .hide-all').click(function () {
                        $checkbox.each(function (i, item) {
                            if ($(item).prop('checked')) {
                                $(item).prop('checked', false);
                            }
                        });
                        setFilter.array = [];
                        mapFilter(setFilter, mapArea.objectManager);
                    });
                    var $showBtn = $('.area-description__show');
                    var $areaDescriptionContent = $('.area-description__content');
                    var $areaDescription = $('.area-description');
                    $showBtn.click(function () {
                        $showBtn.toggleClass('hide');
                        $showBtn.toggleClass('visible');
                        $areaDescriptionContent.toggleClass('area-description__content_visible');
                        $areaDescription.toggleClass('area-description_active');
                    });
                });
            },
        }

        BX.ContractMap.create = function (params)
        {
            var self = new BX.ContractMap();
            self.initialize(params);
        };
}


if (typeof(BX.RegisterDate) === "undefined")
{

    BX.RegisterDate = function ()
    {
        this.url = "";
        this.dateRegisterBtn = BX('dateRegisterBtn');
        this.inputRegisterDate = BX('inputRegisterDate');
        this.ajaxURL = "/index.php";
        this.modalPopup = $('#modal_result');
    };

    BX.RegisterDate.prototype =
        {
            initialize: function (url)
            {
                this.url = url;
                BX.addCustomEvent('RegisterFormData', BX.delegate(this._handleRegisterFormData, this));
                this._EventsRegister();
                this.SetMusk();
                this.SetStyles();
            },
            _EventsRegister: function(){
                var self = this;
                //форма Расписание
                BX.bind(this.dateRegisterBtn, 'click',function() {BX.onCustomEvent('RegisterFormData')});
                //переключатели недель
                $(document).on('click', '#nextWeek, #prevWeek', function (e) {
                    var weekType = $(this).attr('id');
                    self._handleRegisterFormData(weekType);
                });
                //выбор времеени
                $(document).on('click', 'a[data-type=registry]', function (e) {
                    e.preventDefault();
                    var dateRegistry = $(this).attr('data-date');

                    self.inputRegisterDate.value = dateRegistry;
                    self.modalPopup.empty();
                })
                //скрытие формы Расписание
                $(document).on('click', '.mfp-close', function (e){
                    self.modalPopup.empty();
                })
            },

            _handleRegisterFormData: function(weekType = ''){
                var post = {};
                var self = this;
                post['weekType'] = weekType;
                BX.ajax.post(
                    this.url+"/registerData.php",
                    post,
                    function (data) {
                        self.modalPopup.empty();
                        self.modalPopup.html(data);
                    }
                );

            },

            SetMusk:function () {
                $('[data-type="phone"]').mask("+7(999) 999-99-99");
                $('[data-type="s_pasport"]').mask("9999 999999");
            },
            SetStyles:function () {
                var selects = document.querySelectorAll('.inputBlock select')
                for (const select of selects) {
                    select.className += " input col-6";
                }
            },
        },

        BX.RegisterDate.create = function (url)
        {
            var self = new BX.RegisterDate();
            self.initialize(url);
        };
}
<template>
    <div class="login-container" :style="{height:height}" v-loading="page_loading">
        <vue-particle-line>
            <el-form ref="loginForm" :model="loginForm" :rules="loginRules" class="login-form" auto-complete="on" label-position="left">

                <div class="title-container">
                    <h3 class="title">管理平台</h3>
                </div>

                <el-form-item prop="username">
                    <span class="svg-container">
                      <svg-icon icon-class="user" />
                    </span>
                    <el-input
                            ref="username"
                            v-model="loginForm.username"
                            placeholder="Username"
                            name="username"
                            type="text"
                            tabindex="1"
                            auto-complete="on"
                    />
                </el-form-item>

                <el-form-item prop="password">
                    <span class="svg-container">
                        <svg-icon icon-class="password" />
                    </span>
                    <el-input
                            :key="passwordType"
                            ref="password"
                            v-model="loginForm.password"
                            :type="passwordType"
                            placeholder="Password"
                            name="password"
                            tabindex="2"
                            auto-complete="on"
                            @keyup.enter.native="handleLogin"
                    />
                    <span class="show-pwd" @click="showPwd">
                        <svg-icon :icon-class="passwordType === 'password' ? 'eye' : 'eye-open'" />
                    </span>
                </el-form-item>

                <el-button :loading="loading" type="primary" style="width:100%;margin-bottom:30px;" @click.native.prevent="handleLogin">Login</el-button>

                <div class="tips">
                    <span style="margin-right:20px;">username: admin</span>
                    <span> password: admin123</span>
                </div>

                <div class="login_type" style="text-align: center;font-size:35px;">
                    <svg-icon icon-class="wechat_login" @click="showWechatLogin" title="微信扫码登陆"/>
                </div>

            </el-form>
        </vue-particle-line>

        <el-dialog :visible.sync="wechat.wechat_visible" title="微信扫码登陆" width="320px" @close="wechat_close">
            <div style="text-align: center;" v-loading="wechat.qrcode_loading">
                <img :src="wechat.qrcode" >
                <el-radio-group v-model="wechat.mode" size="small" style="margin-top:9px;" @change="handleChangeMode">
                    <el-radio label="web" >网页版</el-radio>
                    <el-radio label="h5" >手机版</el-radio>
                </el-radio-group>
            </div>
            <div style="text-align:right;margin-top:10px;">
                <el-button type="danger" @click="wechat.wechat_visible=false">Cancel</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import { validUsername } from '@/utils/validate'
    import QRCode  from 'qrcode'
    import { wechat_login } from '@/api/auth'

    export default {
        name: 'Login',
        components: {},
        data() {
            const validateUsername = (rule, value, callback) => {
                callback()
            }
            const validatePassword = (rule, value, callback) => {
                if (value.length < 6) {
                    callback(new Error('The password can not be less than 6 digits'))
                } else {
                    callback()
                }
            }
            return {
                loginForm: {
                    username: 'admin',
                    password: 'admin123'
                },
                loginRules: {
                    username: [{ required: true, trigger: 'blur', validator: validateUsername }],
                    password: [{ required: true, trigger: 'blur', validator: validatePassword }]
                },
                height:'0px',
                loading: false,
                passwordType: 'password',
                redirect: undefined,
                page_loading:false,
                wechat:{
                    wechat_visible:false,
                    qrcode:'',
                    state:'',
                    mode:'web',
                    qrcode_loading:'',
                    interval:null,
                },
            }
        },
        watch: {
            $route: {
                handler: function(route) {
                    this.redirect = route.query && route.query.redirect
                },
                immediate: true
            }
        },
        methods: {
            showPwd() {
                if (this.passwordType === 'password') {
                    this.passwordType = ''
                } else {
                    this.passwordType = 'password'
                }
                this.$nextTick(() => {
                    this.$refs.password.focus()
                })
            },
            handleLogin() {
                this.$refs.loginForm.validate(valid => {
                    if (valid) {
                        this.loading = true

                        this.$store.dispatch('user/login', this.loginForm).then((response) => {
                            this.loading = false
                            if( response.data.code == 1 ){
                                this.$router.push({ path: this.redirect || '/' })
                                this.$message('登录成功');
                            }else{
                                this.$message(response.data.msg);
                            }

                            //console.log(response.data);
                        }).catch((e) => {
                            console.log(e);
                            this.loading = false
                        })
                    } else {
                        console.log('error submit!!')
                        return false
                    }
                })
            },
            async showWechatLogin(){
                this.page_loading = true;
                this.wechat.qrcode = '';
                this.wechat.state = '';

                let res = await this.handleWechatLogin();

                this.page_loading = false;

                if( res ){
                    this.wechat.wechat_visible = true;
                    this.wechat_start();
                }
            },
            async handleWechatLogin(){
                let _this = this;

                let res = await wechat_login(_this.wechat.state,_this.wechat.mode);

                console.log(res.data);

                if( res.data.code == 1 ){
                    _this.init( res.data.data.token );
                    return true;
                }else if(res.data.code == 2){
                    let opts = {
                        errorCorrectionLevel: 'H',
                        type: 'image/jpeg',
                        rendererOpts: {
                            quality: 1
                        },
                        width:250,
                        margin:0
                    }

                    _this.wechat.state = res.data.data.state;

                    QRCode.toDataURL(res.data.data.qrcode, opts, function (err, url) {
                        if (err) throw err

                        _this.wechat.qrcode = url;
                    });

                    return true;
                }else{
                    if( this.wechat.state == '' ){
                        this.$message( res.data.msg ,'error');
                    }
                }

                return false;
            },
            async handleChangeMode(){
                this.wechat.qrcode_loading = true;
                this.wechat_close();
                let res = await this.handleWechatLogin(this.wechat.state,this.wechat.mode)
                console.log(res);
                if( res ){
                    this.wechat_start();
                }
                this.wechat.qrcode_loading = false;
            },
            wechat_start(){
                let _this = this;
                _this.wechat.interval = setInterval(function(){
                    _this.handleWechatLogin(_this.wechat.state,_this.wechat.mode);
                },3000)
            },
            wechat_close(){
                if( this.wechat.interval ){
                    clearInterval(this.wechat.interval);
                }
            },
            init: function( token ) {
                // 检查是否有Token
                token = token || this.$route.query.token;
                if(typeof token == 'string' && token.length > 0){
                    this.$store.commit('user/SET_TOKEN',token);
                    this.$router.push({path:'/'});
                }
            },
        },
        created() {
            document.body.style.backgroundColor = '#1d1c1c';
            this.height = window.innerHeight-20+'px';
            this.init();
        },
        beforeDestroy() {
            document.body.style.backgroundColor = '';
            this.wechat_close();
        }
    }
</script>

<style lang="scss">
    /* 修复input 背景不协调 和光标变色 */
    /* Detail see https://github.com/PanJiaChen/vue-element-admin/pull/927 */

    $bg:#283443;
    $light_gray:#fff;
    $cursor: #fff;

    @supports (-webkit-mask: none) and (not (cater-color: $cursor)) {
        .login-container .el-input input {
            color: $cursor;
        }
    }

    /* reset element-ui css */
    .login-container {
        .el-input {
            display: inline-block;
            height: 47px;
            width: 85%;

            input {
                background: transparent;
                border: 0px;
                -webkit-appearance: none;
                border-radius: 0px;
                padding: 12px 5px 12px 15px;
                color: $light_gray;
                height: 47px;
                caret-color: $cursor;

                &:-webkit-autofill {
                    box-shadow: 0 0 0px 1000px $bg inset !important;
                    -webkit-text-fill-color: $cursor !important;
                }
            }
        }

        .el-form-item {
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            color: #454545;
        }

        .el-dialog__body{
            padding:6px 20px 15px 20px;
        }
    }
</style>

<style lang="scss" scoped>
    $bg:#2d3a4b;
    $dark_gray:#889aa4;
    $light_gray:#eee;

    .login-container {
        min-height: 100%;
        width: 100%;
        //background-color: $bg;
        overflow: hidden;

        .login-form {
            position: relative;
            width: 520px;
            max-width: 100%;
            padding: 160px 35px 0;
            margin: 0 auto;
            overflow: hidden;
            z-index:100;
        }

        .tips {
            font-size: 14px;
            color: #fff;
            margin-bottom: 10px;

            span {
                &:first-of-type {
                    margin-right: 16px;
                }
            }
        }

        .svg-container {
            padding: 6px 5px 6px 15px;
            color: $dark_gray;
            vertical-align: middle;
            width: 30px;
            display: inline-block;
        }

        .title-container {
            position: relative;

            .title {
                font-size: 26px;
                color: $light_gray;
                margin: 0px auto 40px auto;
                text-align: center;
                font-weight: bold;
            }
        }

        .show-pwd {
            position: absolute;
            right: 10px;
            top: 7px;
            font-size: 16px;
            color: $dark_gray;
            cursor: pointer;
            user-select: none;
        }
    }
</style>
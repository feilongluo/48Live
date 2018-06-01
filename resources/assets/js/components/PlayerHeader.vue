<template>
    <Header>
        <Menu mode="horizontal" theme="dark" active-name="1">
            <router-link to="/" class="layout-logo">
                <Icon class="home-icon" type="home"></Icon>
            </router-link>
            <div class="layout-nav">
                <router-link :to="url">
                    <Button type="primary">不能播放，切换播放器</Button>
                </router-link>
            </div>
        </Menu>
    </Header>
</template>

<script>
    export default {
        name:'PlayerHeader',
        props:{
            url:{
                type:String,
                required:true
            }
        }
    }
</script>

<style scoped>
    .layout-nav {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        float: right;
        width: 480px;
        margin: 0 auto;
    }

    .layout-logo {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 4px 8px;
        background: #5b6270;
        float: left;
        position: relative;
        top: 15px;
        left: 20px;
        border-radius: 4px;
    }

    .home-icon {
        font-size: 32px;
        color: white;
    }
</style>
<template>
    <div class="layout">
        <Spin size="large" fix v-if="spinShow"></Spin>

        <Layout>
            <Header>
                <Cascader class="cascader" placeholder="请选择成员" :data="members" v-model="selectedMember"></Cascader>

                <span style="margin-left:16px;color:white;">获取直播条数</span>
                <InputNumber style="margin-left:8px;" :max="1600" :min="1" v-model="limit"></InputNumber>


                <Button type="primary" @click="getList">搜索</Button>
            </Header>
            <Content style="padding: 8px 32px;">
                <Card>
                    <div style="min-height: 200px;">
                        <Tabs type="card">
                            <TabPane label="直播">
                                <Card v-if="currentLiveList.length === 0" style="margin-bottom:8px">
                                    <p slot="title">当前没有直播</p>
                                </Card>

                                <Row v-for="index in Math.ceil(currentLiveList.length / 8)"
                                        :key="index">
                                    <Col style="padding: 4px;" span="3" v-for="(item, i) in currentLiveList"
                                            v-if="i <  index * 8 && i >= (index - 1) * 8" :key="item.liveId">
                                        <router-link :to="getUrl(item)">
                                            <Card>
                                                <p slot="title">{{item.subTitle}}</p>

                                                <img ref="cover" class="cover" :src="cover(item.cover)">
                                                <p style="color:#ccc;">{{item.date}}</p>
                                                <p>{{item.member.name}}</p>
                                            </Card>
                                        </router-link>
                                    </Col>
                                </Row>
                                <Page :total="liveTotal" :page-size="pageSize" size="small" show-total
                                        @on-change="onPageChange"></Page>
                            </TabPane>

                            <TabPane label="回放">
                                <Row v-for="index in Math.ceil(currentReviewList.length / 8)"
                                        :key="index">
                                    <Col style="padding: 4px;" span="3" v-for="(item, i) in currentReviewList"
                                            v-if="i <  index * 8 && i >= (index - 1) * 8" :key="item.liveId">
                                        <router-link :to="getUrl(item)">
                                            <Card>
                                                <p slot="title">{{item.subTitle}}</p>

                                                <img ref="cover" class="cover" :src="cover(item.cover)">
                                                <p style="color:#ccc;">{{item.date}}</p>
                                                <p>
                                                    <span style="color: #000;">{{item.member.name}}</span>
                                                    <span class="team-badge"
                                                            :style="{'background-color':item.member.team.color}">{{item.member.team.name}}</span>
                                                </p>
                                            </Card>
                                        </router-link>
                                    </Col>
                                </Row>
                                <Page :total="reviewTotal" :page-size="pageSize" size="small" show-total
                                        @on-change="onPageChange"></Page>
                            </TabPane>
                        </Tabs>
                    </div>
                </Card>
            </Content>
            <Footer class="layout-footer-center">2011-2016 &copy; TalkingData</Footer>
        </Layout>
    </div>
</template>

<script>
    import {Member, groups} from '../48infos';

    export default {
        name:'Home',
        data(){
            return {
                spinShow:true,
                liveList:[],
                reviewList:[],
                currentLiveList:[],
                currentReviewList:[],
                coverWidth:-1,
                pageSize:16,
                members:[],
                selectedMember:[],
                limit:800,
                liveTotal:0,
                reviewTotal:0
            }
        },
        created:function(){
            this.getList();

            this.members = groups.map(group =>{
                return {
                    value:group.id,
                    label:group.name,
                    children:group.teams.map(team =>{
                        return {
                            value:team.id,
                            label:team.fullname,
                            children:team.members().map(member =>{
                                return {
                                    value:member.id,
                                    label:member.name
                                }
                            })
                        }
                    })
                };
            });
        },
        updated:function(){
            if(this.coverWidth === -1){
                this.coverWidth = this.$refs.cover[0].offsetWidth;
            }

            this.$refs.cover.forEach(item =>{
                item.style.height = this.coverWidth + 'px';
            });
        },
        methods:{
            getList:function(){
                this.spinShow = true;
                axios.get('/api/live', {
                    params:{
                        memberId:this.selectedMember[2],
                        limit:this.limit
                    }
                }).then(res =>{
                    if(res.data.errorCode === 0){
                        this.liveList = res.data.data.liveList.map(item =>{
                            const pictures = item.picPath.split(',');
                            item.cover = pictures[0];
                            item.member = new Member(item.memberId);
                            item.date = new Date(item.startTime).format('yyyy-MM-dd hh:mm');
                            return item;
                        });
                        this.liveTotal = this.liveList.length;

                        this.reviewList = res.data.data.reviewList.map(item =>{
                            const pictures = item.picPath.split(',');
                            item.cover = pictures[0];
                            item.member = new Member(item.memberId);
                            item.date = new Date(item.startTime).format('yyyy-MM-dd hh:mm');
                            return item;
                        });
                        this.reviewTotal = this.reviewList.length;

                        this.onPageChange(1);
                        this.spinShow = false;
                    }else{
                        this.spinShow = false;
                    }
                }).catch(error =>{
                    this.spinShow = false;
                    console.log(error);
                });
            },
            onPageChange:function(page){
                const start = (page - 1) * 12;
                this.currentLiveList = this.liveList.slice(start, start + this.pageSize);
                this.currentReviewList = this.reviewList.slice(start, start + this.pageSize);
            },
            getUrl:function(item){
                if(item.streamPath.includes('.flv') || item.streamPath.includes('.mp4')){
                    return '/flvjs/' + item.liveId;
                }else if(item.streamPath.includes('.m3u8')){
                    return '/videojs/' + item.liveId;
                }else{
                    return '/';
                }
            },
            cover:function(cover){
                if(cover.indexOf('http') >= 0){
                    return cover;
                }else{
                    return 'https://source.48.cn' + cover;
                }
            }
        }
    }

    Date.prototype.format = function(fmt){
        const o = {
            "M+":this.getMonth() + 1,                 //月份
            "d+":this.getDate(),                    //日
            "h+":this.getHours(),                   //小时
            "m+":this.getMinutes(),                 //分
            "s+":this.getSeconds(),                 //秒
            "q+":Math.floor((this.getMonth() + 3) / 3), //季度
            "S":this.getMilliseconds()             //毫秒
        };
        if(/(y+)/.test(fmt)){
            fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
        }
        for(const k in o){
            if(new RegExp("(" + k + ")").test(fmt)){
                fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? (o[k]) :
                    (("00" + o[k]).substr(("" + o[k]).length)));
            }
        }
        return fmt;
    }
</script>

<style scoped>
    .cover {
        width: 100%;
    }

    .layout-footer-center {
        text-align: center;
    }

    .team-badge {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-width: 32px;
        padding: 0 4px;
        border-radius: 24px;
        color: white;
        font-size: 12px;
    }

    .cascader {
        display: inline-flex;
        min-width: 240px;
    }
</style>
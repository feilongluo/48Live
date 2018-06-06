import './48sdk/base-v2.8.0';
import './48sdk/nim-v2.8.0';
import axios from 'axios';
import SDKChatRoom from './48sdk/chatroom-v2.8.0';

class ChatRoom {
    constructor(options){
        axios.get('/api/token').then(response =>{
            if(options.onTokenSuccess !== undefined){
                options.onTokenSuccess();
            }
            this.chatRoom = new SDKChatRoom({
                appKey:'632feff1f4c838541ab75195d1ceb3fa',      //从官网公演直播网页代码获取
                account:response.data.data.account,
                token:response.data.data.token,
                chatroomId:options.roomId,
                chatroomAddresses:[
                    'weblink04.netease.im:443',
                    /*'',*/
                ],
                onconnect:options.onConnect,
                onerror:options.onError,
                onwillreconnect:options.onWillConnnect,
                ondisconnect:options.onDisconnect,
                // // 消息
                onmsgs:options.onMessage
            });
        }).catch(error =>{
            if(options.onTokenFailed !== undefined){
                options.onTokenFailed(error);
            }
        });
    }
}

export default ChatRoom;
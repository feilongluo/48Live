import Home from './components/Home';
import FlvJs from './components/FlvJs';
import VideoJs from './components/VideoJs';
import Mp4 from './components/Mp4';

const routes = [
    {
        path:'/',
        components:{
            default:Home
        }
    },
    {
        path:'/flvjs/:liveId',
        components:{
            default:FlvJs
        }
    },
    {
        path:'/videojs/:liveId',
        components:{
            default:VideoJs
        }
    },
    {
        path:'/mp4',
        components:{
            default:Mp4
        }
    }
];

export default routes;
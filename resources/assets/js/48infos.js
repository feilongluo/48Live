import Tools from './tools';

let members = [],
    teams = [];

Tools.getMembers().then(result =>{
    members = result;
}).catch(error =>{
    console.log(error);
});

Tools.getTeams().then(result =>{
    teams = result;
}).catch(error =>{
    console.log(error);
});


const groupsObj = {"10":"SNH48", "11":"BEJ48", "12":"GNZ48", "13":"SHY48", "14":"CKG48"};

class Member {
    constructor(memberId){
        this.id = memberId;
        const member = members.find(item =>{
            return item.member_id == memberId;
        });
        this.name = member.real_name;
        this.team = new Team(member.team_id);
    }
}

class Team {
    constructor(teamId){
        const team = teams.find(item =>{
            return item.team_id == teamId;
        });
        if(team === undefined){
            console.log(teamId);
            return;
        }
        this.id = teamId;
        this.fullname = team.team_name;
        this.name = this.fullname.replace('TEAM ', '');
        this.color = '#' + team.color;
    }

    members(){
        const a = [];
        members.forEach(item =>{
            if(item.team_id == this.id){
                a.push(new Member(item.member_id));
            }
        });
        return a;
    }
}

class Group {
    constructor(groupId){
        this.id = groupId;
        this.name = groupsObj[groupId];
        this.teams = [];
        for(let team of teams){
            if(team.group_id == groupId){
                this.teams.push(new Team(team.team_id));
            }
        }
    }
}

const groups = [];
for(const id in groupsObj){
    groups.push(new Group(id));
}

export {groups, teams, members, Member, Team, Group};
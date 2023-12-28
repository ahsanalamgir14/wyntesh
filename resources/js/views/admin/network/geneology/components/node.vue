<template>
  <li v-if="node.id">
    <router-link :to="'/network/geneology/member/'+node.user.username">
      <div class="member-view-box">
        <div class="member-image">      
            <div slot="reference" v-if="node.user.gender=='f'">
              <img class="gen-image" v-if="node.user.is_active && node.kyc.verification_status=='verified' " src="@/assets/images/female-active.jpeg" alt="Member">
              <img class="gen-image" v-else-if="node.user.is_active && node.kyc.verification_status!='verified' " src="@/assets/images/female-kyc-pending.jpeg" alt="Member">
              <img class="gen-image" v-else="!node.user.is_active" src="@/assets/images/female-inactive.jpeg" alt="Member">
            </div>
            <div slot="reference" v-else-if="node.user.gender=='o'">
              <img class="gen-image" v-if="node.user.is_active && node.kyc.verification_status=='verified' " src="@/assets/images/trans-active.jpeg" alt="Member">
              <img class="gen-image" v-else-if="node.user.is_active && node.kyc.verification_status!='verified' " src="@/assets/images/trans-kyc-pending.jpeg" alt="Member">
              <img class="gen-image" v-else="!node.user.is_active" src="@/assets/images/trans-inactive.jpeg" alt="Member">
            </div>
            <div slot="reference" v-else>
              <img class="gen-image" v-if="node.user.is_active && node.kyc.verification_status=='verified' " src="@/assets/images/male-active.jpeg" alt="Member">
              <img class="gen-image" v-else-if="node.user.is_active && node.kyc.verification_status!='verified' " src="@/assets/images/male-kyc-pending.jpeg" alt="Member">
              <img class="gen-image" v-else="!node.user.is_active" src="@/assets/images/male-inactive.jpeg" alt="Member">
            </div>                   
        </div>
        <div class="member-details">
            <h3 class="text-sm font-bold">{{node.user?node.user.name:''}}</h3>
            <h4 class="text-xs leading-none font-bold">{{node.user?node.user.username:''}}</h4>
          </div>
      </div>
    </router-link>

        <el-popover
            placement="top-start"
            
            width="315"
            trigger="click"
              >
            <div slot="reference">
               <el-button size="mini" round type="warning">Details</el-button>
            </div>

            <div class="pop-over">

              <p class="mt-2"><b>{{node.user.name}}</b></p>                   
              <p class="mt-2"><b>ID</b> : {{node.user.username}}</p>
              <p class="mt-2"><b>Wallet Balance</b> : {{node.wallet_balance}}</p>
              <p class="mt-2"><b>Rank</b> : {{node.rank.name}}</p>
              <p class="mt-2"><b>Team</b> : {{node.team_size}}</p>
              <p class="mt-2"><b>Total Self PV</b> : {{node.total_personal_pv?node.total_personal_pv:0}}</p>
              <p class="mt-2"><b>Total Group PV</b> : {{node.group_pv?node.group_pv:0}}</p>
              <p class="mt-2"><b>Total Carry Forward Available</b> : {{node.member_payout?node.member_payout.total_carry_forward_bv:0}}</p>
              <p class="mt-2"><b>KYC Status</b> : {{node.kyc.verification_status}}</p>

            </div>
        </el-popover>

    <ul v-if="node.hasOwnProperty('children')">
      <node v-for="child in childs" :key="node.id+rand()" :node="child"></node>
    </ul>
  </li>
  <li v-else>
     <router-link :to="'/network/add?parent_code='+node.parent_code+'&position='+node.position">
      <div class="member-view-box">
        <div class="member-image">
          <img class="gen-image" src="@/assets/images/add.jpg" alt="Member">
          <div class="member-details">
            <h3>Place Here</h3>
          </div>
        </div>
      </div>
    </router-link>
  </li>
</template>
<script>

import defaultSettings from '@/settings';
const { totalLegs } = defaultSettings;
import { parseTime } from "@/utils";

export default {
  name: "node",
  props: {
    node: Object
  },
  created(){

    if(this.node.children){
      for(var i=1; i <= totalLegs; i++){
        let ch=this.node.children.find(obj => {
          return obj.position === i
        });

        if(ch){
          this.childs.push(ch);
        }else{
          this.childs.push({id:0,position:i,parent_code:this.node.user.username})
        }
      }  
    }
   
  },
  beforeUpdate(){
   
    for(var i=1; i <= totalLegs; i++){
      let ch=this.node.children.find(obj => {
        return obj.position === i
      });

      if(ch){
        this.childs.push(ch);
      }else{
        this.childs.push({id:0,position:i,parent_code:this.node.user.username})
      }
    }
   
  },
  data() {
    return { 
      childs:[],
    };
  },
  methods:{
    rand(){
      return Math.random();
    },   
  }
};
</script>

<style scoped lang="scss">
/*----------------geneology-scroll----------*/

.pop-over p{
    margin-top: 0.5rem;
}
.member-details {
  h3 {
    margin-bottom: 2px;
    margin-top: 2px;
  }

  h4 {
    margin-bottom: 2px;
    margin-top: 2px;
  }
}

.gen-image {
  border-radius: 100px;
}

.geneology-scroll::-webkit-scrollbar {
  width: 5px;
  height: 8px;
}

.geneology-scroll::-webkit-scrollbar-track {
  border-radius: 10px;
  background-color: #e4e4e4;
}

.geneology-scroll::-webkit-scrollbar-thumb {
  background: #212121;
  border-radius: 10px;
  transition: 0.5s;
}

.geneology-scroll::-webkit-scrollbar-thumb:hover {
  background: #d5b14c;
  transition: 0.5s;
}


/*----------------geneology-tree----------*/
.geneology-body {
  white-space: nowrap;
  overflow-y: hidden;
  padding: 50px;
  min-height: 500px;
  padding-top: 10px;
}

.geneology-tree ul {
  padding-top: 20px;
  position: relative;
  padding-left: 0px;
  display: flex;
}

.geneology-tree li {
  float: left;
  text-align: center;
  list-style-type: none;
  position: relative;
  padding: 20px 5px 0 5px;
}

.geneology-tree li::before,
.geneology-tree li::after {
  content: '';
  position: absolute;
  top: 0;
  right: 50%;
  border-top: 2px solid #ccc;
  width: 50%;
  height: 18px;
}

.geneology-tree li::after {
  right: auto;
  left: 50%;
  border-left: 2px solid #ccc;
}

.geneology-tree li:only-child::after,
.geneology-tree li:only-child::before {
  display: none;
}

.geneology-tree li:only-child {
  padding-top: 0;
}

.geneology-tree li:first-child::before,
.geneology-tree li:last-child::after {
  border: 0 none;
}

.geneology-tree li:last-child::before {
  border-right: 2px solid #ccc;
  border-radius: 0 5px 0 0;
  -webkit-border-radius: 0 5px 0 0;
  -moz-border-radius: 0 5px 0 0;
}

.geneology-tree li:first-child::after {
  border-radius: 5px 0 0 0;
  -webkit-border-radius: 5px 0 0 0;
  -moz-border-radius: 5px 0 0 0;
}

.geneology-tree ul ul::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  border-left: 2px solid #ccc;
  width: 0;
  height: 20px;
}

.geneology-tree li a {
  text-decoration: none;
  color: #666;
  font-family: arial, verdana, tahoma;
  font-size: 11px;
  display: inline-block;
  border-radius: 5px;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
}

.geneology-tree li a:hover+ul li::after,
.geneology-tree li a:hover+ul li::before,
.geneology-tree li a:hover+ul::before,
.geneology-tree li a:hover+ul ul::before {
  border-color: #fbba00;
}

/*--------------memeber-card-design----------*/
.member-view-box {
  padding: 0px 60px;
  text-align: center;
  border-radius: 4px;
  position: relative;
}

.member-image {
    width: 60px;
    position: relative;
    margin: auto;
    display: block;
}

.member-image img {
  width: 60px;
  height: 60px;
  border-radius: 50px;
  z-index: 1;
}

.pop-over {
  padding: 5px !important;
}



</style>

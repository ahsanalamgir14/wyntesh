<template>
    <li v-if="node.id">
      <router-link :to="'/geneology/member/'+node.user.username">
          <div class="member-view-box">
              <div class="member-image">
                <el-popover
                  placement="top-start"
                  :title="node.user.name"
                  width="200"
                  trigger="hover"
                    >
                  <div slot="reference">
                    <img v-if="node.user.is_active && node.kyc.verification_status=='verified' " src="@/assets/images/active.png" alt="Member">
                    <img v-else-if="node.user.is_active && node.kyc.verification_status!='verified' " src="@/assets/images/kyc-pending.png" alt="Member">
                    <img v-else="!node.user.is_active" src="@/assets/images/deactive.png" alt="Member">
                  </div>
                  <div>                   
                    <p><b>ID</b> : {{node.user.username}}</p>
                    <p><b>Wallet Balance</b> : {{node.wallet_balance}}</p>
                    <p><b>KYC Status</b> : {{node.kyc.verification_status}}</p>
                  </div>
                </el-popover>

                  
                  <div class="member-details">
                      <h3>{{node.user?node.user.name:''}}</h3>
                      <h4>{{node.user?node.user.username:''}}</h4>
                  </div>
              </div>
          </div>
      </router-link>
      <ul v-if="node.hasOwnProperty('children')">
          <node v-for="child in childs" :key="node.id+rand()" :node="child" ></node>        
      </ul>
    </li>
    <li v-else >
        <router-link :to="'/members/add?sponsor_code='+node.sponsor_code+'&position='+node.position">
          <div class="member-view-box">
              <div class="member-image">
                  <img src="@/assets/images/add.png" alt="Member">
                  <div class="member-details">
                      <h3>Add<br>Here</h3>
                  </div>
              </div>
          </div>
        </router-link>
    </li>
</template>

<script>

import defaultSettings from '@/settings';
const { totalLegs } = defaultSettings;

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
          this.childs.push({id:0,position:i,sponsor_code:this.node.user.username})
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
        this.childs.push({id:0,position:i,sponsor_code:this.node.user.username})
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
.member-details{
  h3{
    margin-bottom: 2px;
    margin-top: 2px;
  }
  h4{
    margin-bottom: 2px;
    margin-top: 2px;
  }
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
.geneology-body{
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
    float: left; text-align: center;
    list-style-type: none;
    position: relative;
    padding: 20px 5px 0 5px;
}
.geneology-tree li::before, .geneology-tree li::after{
    content: '';
    position: absolute; 
  top: 0; 
  right: 50%;
    border-top: 2px solid #ccc;
    width: 50%; 
  height: 18px;
}
.geneology-tree li::after{
    right: auto; left: 50%;
    border-left: 2px solid #ccc;
}
.geneology-tree li:only-child::after, .geneology-tree li:only-child::before {
    display: none;
}
.geneology-tree li:only-child{ 
    padding-top: 0;
}
.geneology-tree li:first-child::before, .geneology-tree li:last-child::after{
    border: 0 none;
}
.geneology-tree li:last-child::before{
    border-right: 2px solid #ccc;
    border-radius: 0 5px 0 0;
    -webkit-border-radius: 0 5px 0 0;
    -moz-border-radius: 0 5px 0 0;
}
.geneology-tree li:first-child::after{
    border-radius: 5px 0 0 0;
    -webkit-border-radius: 5px 0 0 0;
    -moz-border-radius: 5px 0 0 0;
}
.geneology-tree ul ul::before{
    content: '';
    position: absolute; top: 0; left: 50%;
    border-left: 2px solid #ccc;
    width: 0; height: 20px;
}
.geneology-tree li a{
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
.geneology-tree li a:hover+ul ul::before{
    border-color:  #fbba00;
}

/*--------------memeber-card-design----------*/
.member-view-box{
    padding:0px 20px;
    text-align: center;
    border-radius: 4px;
    position: relative;
}
.member-image{
    width: 60px;
    position: relative;
}
.member-image img{
  width: 60px;
  height: 60px;
  border-radius: 6px;  
  z-index: 1;
}


</style>
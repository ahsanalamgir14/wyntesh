<template>
  <div class="dashboard-editor-container">
  <el-alert
    v-if="notice.is_active"
    style="margin-bottom: 10px;"
    :title="notice.title"
    type="success"
    :description="notice.description"
    show-icon>
  </el-alert>
  <el-alert
    v-if="!temp.is_active"
    style="margin-bottom: 10px;"
    title="ID Activation Notice"
    type="error"
    description="Your ID is inactive, kindly fullfill minimum business criteria to activate ID"
    show-icon>
  </el-alert>


    <el-row :gutter="10" >      
      <el-col :xs="24" :sm="24" :md="6" :lg="6" >
        <el-card style="margin-bottom: 5px;" shadow="never">
          <div class="user-profile">
            <div class="user-name text-center">
              <span style="font-size: 20px;">Welcome</span>
            </div>
            <div class="box-center">
              <div class="user-name text-center" >
                <span >{{ temp.name }}</span>
                <br>
                <div style="margin-top: 10px;"><span >{{ temp.username }}</span></div>
                <div style="margin-top: 10px;">
                  <el-tag style="font-size: 16px;" type="primary" >{{ temp.member.rank.name }}</el-tag>
                  <!-- <el-tag style="font-size: 16px;" type="primary" >Customer</el-tag> -->
                </div>
                <div class="avatar-wrapper">
                    <img :src="temp.profile_picture?temp.profile_picture:'/images/avatar.png'" class="user-avatar" style="margin-left: 20px;">
                </div>



                <div style="margin-top: 10px;">
                  <el-tag v-if="temp.kyc.verification_status=='verified'" type="success">Verified</el-tag>
                  <el-tag v-if="temp.kyc.verification_status=='pending'" type="warning">KYC - Pending</el-tag>
                  <el-tag v-if="temp.kyc.verification_status=='submitted'" type="primary">KYC - Submitted</el-tag>
                  <el-tag v-if="temp.kyc.verification_status=='rejected'" type="danger">KYC - Rejected</el-tag>
                </div>
              </div>
              <div class="user-role text-center text-muted">
                <h4 style="margin-bottom:7px ">Joined on</h4>
                {{ temp.created_at | parseTime('{y}-{m}-{d}') }}
              </div>
              <div style="margin-top:10px;">
                <el-button type="warning" style="width: 100%" size="mini" round v-clipboard:copy="referral_link" v-clipboard:success="onCopy" >Copy referral link</el-button>
              </div>                
            </div> 
          </div>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="18" :lg="18" >
        <el-row :gutter="10" class="panel-group">
          
          <!-- <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/network/referrals">
              <div class="card-panel gr7" >
                <div class="card-panel-icon-wrapper icon-money">
                  <i class="fas fa-users card-panel-icon"  ></i>
                </div>
                <div class="card-panel-description">
                  
                  <count-to :start-val="0" :end-val="dashboardStats.referrals" :duration="3200" class="card-panel-num" />
                  <div class="card-panel-text">
                    Referrals
                  </div>
                </div>
              </div>
            </router-link>
          </el-col> -->
            <el-col :xs="24" :sm="24" :lg="24" class="card-panel-col">
                <router-link to="/member/payouts/all">
                  <div class="card-panel gr2" >
                    <div class="card-panel-icon-wrapper icon-money payout" style="width:100px;">
                      <i class="fas fa-rupee-sign card-panel-icon"  ></i>
                    </div>
                    <div class="card-panel-description" style="float: left;margin-left: 14px; width: 98%;">
                      
                      <count-to :start-val="0" :end-val="parseFloat(dashboardStats.total_payout)" :duration="3200" class="card-panel-num" />
                      <div class="card-panel-text">
                        Total Payout
                      </div>
                    </div>
                  </div>
                </router-link>
            </el-col>

          <!--   <el-col :xs="24" :sm="24" :lg="8" class="card-panel-col">
                <router-link to="/member/payouts/all">
                  <div class="card-panel gr2" >
                    <div class="card-panel-icon-wrapper icon-money payout" style="width:100px;">
                      <i class="fas fa-rupee-sign card-panel-icon"  ></i>
                    </div>
                    <div class="card-panel-description" style="float: left;margin: 15px 11px;width: 95%;">
                      
                      <count-to :start-val="0" :end-val="parseFloat(dashboardStats.total_reward)" :duration="3200" class="card-panel-num" />
                      <div class="card-panel-text">
                        Total Reward
                      </div>
                    </div>
                  </div>
                </router-link>
            </el-col> -->
            <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/network/downlines">
              <div class="card-panel gr2" >
                <div class="card-panel-icon-wrapper " >
                  <i class="fas fa-users card-panel-icon"  ></i>
                </div>
                <div class="card-panel-description">
                    <count-to :start-val="0" :end-val="dashboardStats.downlines" :duration="2600" class="card-panel-num" />
                  <div class="card-panel-text">
                    Downlines
                  </div>
                </div>
              </div>
            </router-link>
          </el-col>
          <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/wallet/income-wallet">
              <div class="card-panel gr2" >
                <div class="card-panel-icon-wrapper icon-message">
                  <i class="fas fa-wallet card-panel-icon"  ></i>
                </div>
                <div class="card-panel-description">
                  
                  <count-to :start-val="0" :end-val="parseFloat(dashboardStats.income_wallet_balance)" :duration="3000" class="card-panel-num" />
                  <div class="card-panel-text">
                    Income wallet
                  </div>
                </div>
              </div>
            </router-link>
          </el-col>

          <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/wallet/wallet">
              <div class="card-panel gr2" >
                <div class="card-panel-icon-wrapper icon-message">
                  <i class="fas fa-wallet card-panel-icon"  ></i>
                </div>
                <div class="card-panel-description">
                  
                  <count-to :start-val="0" :end-val="dashboardStats.balance" :duration="3000" class="card-panel-num" />
                  <div class="card-panel-text">
                    E Wallet
                  </div>
                </div>
              </div>
            </router-link>
          </el-col>
          
         <!--  <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/wallet/withdrawals">
              <div class="card-panel gr2" >
                <div class="card-panel-icon-wrapper icon-message">
                  <i class="fas fa-wallet card-panel-icon"  ></i>
                </div>
                <div class="card-panel-description">
                  
                  <count-to :start-val="0" :end-val="dashboardStats.withdrawals" :duration="3000" class="card-panel-num" />
                  <div class="card-panel-text">
                    Total Withdrawals
                  </div>
                </div>
              </div>
            </router-link>
          </el-col> -->
         <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/member/payouts/incomes">
                <div class="card-panel gr2" >
                    <div class="card-panel-icon-wrapper icon-shopping">
                      <i class="fas fa-rupee-sign card-panel-icon"  ></i>
                    </div>
                    <div class="card-panel-description">
                        <count-to :start-val="0" :end-val="parseInt(dashboardStats.affiliateIncome)" :duration="3600" class="card-panel-num" />
                        <div class="card-panel-text">
                            Affiliate income
                        </div>
                    </div>
                </div>
            </router-link>
        </el-col>

         <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/member/payouts/incomes">
                <div class="card-panel gr2" >
                    <div class="card-panel-icon-wrapper icon-shopping">
                      <i class="fas fa-rupee-sign card-panel-icon"  ></i>
                    </div>
                    <div class="card-panel-description">
                        <count-to :start-val="0" :end-val="parseInt(dashboardStats.squad_bonus)" :duration="3600" class="card-panel-num" />
                        <div class="card-panel-text">
                            Squad income
                        </div>
                    </div>
                </div>
            </router-link>
        </el-col>

         <!--  <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/reports/personal-pv">
              <div class="card-panel gr2" >
                <div class="card-panel-icon-wrapper icon-shopping">
                  <i class="fas fa-flag card-panel-icon"  ></i>
                </div>
                <div class="card-panel-description">
                  
                  <count-to :start-val="0" :end-val="parseInt(dashboardStats.current_personal_pv)" :duration="3600" class="card-panel-num" />
                  <div class="card-panel-text">
                    Current PV
                    </div>
                </div>
              </div>
            </router-link>
          </el-col> -->

          <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/member/payouts/incomes">
              <div class="card-panel gr2" >
                <div class="card-panel-icon-wrapper icon-shopping">
                  <i class="fas fa-rupee-sign card-panel-icon"  ></i>
                </div>
                <div class="card-panel-description">
                  
                  <count-to :start-val="0" :end-val="parseInt(dashboardStats.elevation)" :duration="3600" class="card-panel-num" />
                  <div class="card-panel-text">
                    Elevation income
                  </div>
                </div>
              </div>
            </router-link>
          </el-col>

          <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/member/payouts/incomes">
              <div class="card-panel gr2" >
                <div class="card-panel-icon-wrapper icon-shopping">
                  <i class="fas fa-rupee-sign card-panel-icon"  ></i>
                </div>
                <div class="card-panel-description">
                  
                  <count-to :start-val="0" :end-val="parseInt(dashboardStats.luxury)" :duration="3600" class="card-panel-num" />
                  <div class="card-panel-text">
                   Luxury income
                  </div>
                </div>
              </div>
            </router-link>
          </el-col>
         
          
    <!--       <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/reports/group-and-matching">
              <div class="card-panel gr2" >
                <div class="card-panel-icon-wrapper icon-shopping">
                  <i class="fas fa-coins card-panel-icon"  ></i>
                </div>
                <div class="card-panel-description">
                  
                  <count-to :start-val="0" :end-val="parseInt(dashboardStats.elevation)" :duration="3600" class="card-panel-num" />
                  <div class="card-panel-text">
                    Pro Bonus
                  </div>
                </div>
              </div>
            </router-link>
          </el-col> -->
          
<!-- 
          <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/reports/group-and-matching">
              <div class="card-panel gr2" >
                <div class="card-panel-icon-wrapper icon-shopping">
                  <i class="fas fa-coins card-panel-icon"  ></i>
                </div>
                <div class="card-panel-description">
                  
                  <count-to :start-val="0" :end-val="parseInt(dashboardStats.total_group_bv)" :duration="3600" class="card-panel-num" />
                  <div class="card-panel-text">
                    Total Group PV
                  </div>
                </div>
              </div>
            </router-link>
          </el-col> -->

           <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/member/payouts/incomes">
              <div class="card-panel gr2" >
                <div class="card-panel-icon-wrapper icon-shopping">
                  <i class="fas fa-rupee-sign card-panel-icon"  ></i>
                </div>
                <div class="card-panel-description">
                  
                  <count-to :start-val="0" :end-val="parseInt(dashboardStats.premium)" :duration="3600" class="card-panel-num" />
                  <div class="card-panel-text">
                   Pro Bonus
                  </div>
                </div>
              </div>
            </router-link>
          </el-col>


          <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/reports/group-and-matching">
              <div class="card-panel gr2" >
                <div class="card-panel-icon-wrapper icon-shopping">
                  <i class="fas fa-coins card-panel-icon"  ></i>
                </div>
                <div class="card-panel-description">
                  
                  <count-to :start-val="0" :end-val="parseInt(dashboardStats.self_pv)" :duration="3600" class="card-panel-num" />
                  <div class="card-panel-text">
                    Self PV
                  </div>
                </div>
              </div>
            </router-link>
          </el-col>
          
  <!--         <el-col :xs="12" :sm="12" :lg="8" class="card-panel-col">
            <router-link to="/reports/group-and-matching">
              <div class="card-panel gr2" >
                <div class="card-panel-icon-wrapper icon-shopping">
                  <i class="fas fa-coins card-panel-icon"  ></i>
                </div>
                <div class="card-panel-description">
                  
                  <count-to :start-val="0" :end-val="parseInt(dashboardStats.total_matched)" :duration="3600" class="card-panel-num" />
                  <div class="card-panel-text"> 
                    Total Matched PV
                  </div>
                </div>
              </div>
            </router-link>
          </el-col>
           -->
           
          
        </el-row>
      </el-col>
    </el-row>
    <el-row :gutter="10" >      
      <el-col :xs="24" :sm="24" :md="12" :lg="12">        
        <el-card shadow="never">
          <bar-chart :chartData="payoutData" ></bar-chart>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="12" :lg="12">        
        <el-card shadow="never">
          <line-chart :chartData="orderData" ></line-chart>
        </el-card>
      </el-col>
    </el-row>
    <el-row :gutter="10" >            
      <el-col :xs="24" :sm="24" :md="12" :lg="12">        
        <el-card shadow="never">
          <bar-chart :chartData="downlineData" ></bar-chart>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="12" :lg="12">        
        <el-card shadow="never">
          <line-chart :chartData="referralData" ></line-chart>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="10" style="margin-top: 20px;">      
      <el-col :xs="24" :sm="24" :md="12" :lg="12">        
        <el-card shadow="never">
          <div slot="header" class="clearfix">
            <span>Latest Downlines</span>            
          </div>
          <el-table
            :data="downlines"
            style="width: 100%" border>
            <el-table-column label="Name" min-width="150px">
              <template slot-scope="{row}">
                <span  >{{ row.user.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="ID" min-width="150px">
              <template slot-scope="{row}">
                <span  >{{ row.user.username }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Joining Date" min-width="150px">
              <template slot-scope="{row}">
                <span  >{{ row.user.created_at | parseTime('{y}-{m}-{d}') }}</span>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="12" :lg="12">        
        <el-card shadow="never">
          <div slot="header" class="clearfix">
            <span>Latest Transactions</span>            
          </div>
          <el-table
            :data="transitions"
            style="width: 100%" border>
            <el-table-column label="Type" min-width="150px">
              <template slot-scope="{row}">
                <span  >{{ row.transaction.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Amount" min-width="150px">
              <template slot-scope="{row}">
                <span  >{{ row.amount }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Date" min-width="150px">
              <template slot-scope="{row}">
                <span  >{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-col>
    </el-row>


  </div>
</template>

<script>
import BarChart from './components/BarChart';
import LineChart from './components/LineChart';
import PanThumb from '@/components/PanThumb';
import CountTo from 'vue-count-to';
import { parseTime } from "@/utils";
import { getProfile } from "@/api/user/members";
import { getNotice } from "@/api/user/notices";
import { dashboardStats,orderStats,downlineStats,latestDownlines,latestTransactions,payoutStats,referralStats } from "@/api/user/dashboard";


import defaultSettings from '@/settings';
const { baseUrl } = defaultSettings;

export default {
  name: 'DashboardAdmin',
  components: {
    CountTo,
    BarChart,
    LineChart,
    PanThumb,
  },
  data() {
    return {
      dashboardStats:{},
      downlineData:{},
      payoutData:{},
      referralData:{},
      orderData:{},
      referral_link:'',
      downlines:[],
      transitions:[],
      notice:{
        title:undefined,
        description:undefined,
        is_active:0
      },
      temp:{
          id: undefined,
          name: undefined,
          username: undefined,
          email: undefined,
          password:undefined,
          contact: undefined,
          member:{rank:{name:undefined}},
          gender: "m",
          profile_picture:undefined,
          kyc:{
            address:undefined,
            pincode:undefined,
            adhar:undefined,
            adhar_image:undefined,
            pan:undefined,
            pan_image:undefined,
            pan_cheque:undefined,
            city:undefined,
            state:undefined,
            bank_ac_name:undefined,
            bank_name:undefined,
            bank_ac_no:undefined,
            ifsc:undefined
          },
          parent:undefined,
          comission_from_self:0,
          comission_from_child:0,
          dob: undefined,
          is_active: 0,
      },
    }
  },
  async created() {
    this.getDashboardStats();
    await downlineStats().then(response => {
      this.downlineData = { labels:response.downlines.map(function (el) { return el.date; }), data:response.downlines.map(function (el) { return el.count; }), title:'Downlines', color:'#e39c39' };
    });

    await referralStats().then(response => {
      this.referralData = { labels:response.referrals.map(function (el) { return el.date; }), data:response.referrals.map(function (el) { return el.count; }), title:'Referrals', color:'#e39c39' };
    });

    await latestDownlines().then(response => {
      this.downlines = response.data;
    });

    await getNotice().then(response => {
      this.notice = response.data;
    });

    await latestTransactions().then(response => {
      this.transitions = response.data;
    });

    await orderStats().then(response => {
      this.orderData = { labels:response.sales.map(function (el) { return el.date; }), data:response.sales.map(function (el) { return el.sum; }), title:'Downline Business', color:'#60c402' };
    });

    await payoutStats().then(response => {
      this.payoutData = { labels:response.payouts.map(function (el) { return el.date; }), data:response.payouts.map(function (el) { return el.income; }), title:'Income', color:'#60c402' };
    });
  },
  methods: {
    getDashboardStats(){
      dashboardStats().then(response => {
        this.dashboardStats = response.stats;
        this.temp = this.dashboardStats.member;
        if(!this.temp.kyc){
          this.temp.kyc={
            address:undefined,
            pincode:undefined,
            adhar:undefined,
            pan:undefined,
            city:undefined,
            state:undefined,
            bank_ac_name:undefined,
            bank_name:undefined,
            bank_ac_no:undefined,
            ifsc:undefined
          }
        }
        this.referral_link=baseUrl+'#/register?sponsor_code='+this.temp.username;
      });
    },
    onCopy(){
      this.$notify({
          title: "Copied",
          message: "Referral link copied successfully.",
          type: "success",
          duration: 2000
        })
 
    },
  }
}
</script>

<style lang="scss" scoped>


@media only screen and (max-width: 600px) { 
    .payout{
        width:100% !important ;
    }
        tr{
        th{
            div{
                color:red !important;
            }
        }
    }
    
}

@media only screen and (max-width: 400px) {
   .payout{
        width:100% !important;
    }
    tr{
        th{
            div{
                color:red !important;
            }
        }
    }
}


.dashboard-editor-container {
  padding: 20px;
  background-color: rgb(240, 242, 245);
  position: relative;

  .github-corner {
    position: absolute;
    top: 0px;
    border: 0;
    right: 0;
  }

  .chart-wrapper {
    background: #fff;
    padding: 16px 16px 0;
    margin-bottom: 32px;
  }
}

@media (max-width:1024px) {
  .chart-wrapper {
    padding: 8px;
  }
}


   
    
.panel-group {
  
  .card-panel-col {
    margin-bottom: 32px;
  }

  .card-panel {
    height: 108px;
    cursor: pointer;
    font-size: 12px;
    position: relative;
    overflow: hidden;
    color: #fff;
    border-radius: 10px;
    border:1px solid #ccc;

    .card-panel-icon-wrapper {
      float: left;
      margin: 10px 0 0 10px;
      padding: 5px;
      transition: all 0.38s ease-out;
      border-radius: 6px;
    }

    .card-panel-icon {
      float: left;
      font-size: 35px;
      border-style: solid;
      border-width: thin;
      padding:5px;
      height: 35px;
      width: 45px;
      color:#fff
    }
    @media (min-width:550px) {
      .card-panel-description {
        float: right;
        font-weight: bold;
        margin-top: 15px;
        margin-right: 10px;
        width: 90%;

        .card-panel-text {
          line-height: 25px;
          color: #fff;
          font-size: 14px;
          display: block;
          margin-top: 5px;
        }

        .card-panel-num {
          font-size: 25px;
          float:right;
          display: block;
        }
      }
    }
  }
}

@media (max-width:550px) {
  .card-panel{
    .card-panel-description {
        font-weight: bold;
          margin: 5px auto !important;
          float: none !important;
          text-align: center;
        .card-panel-text {
          line-height: 20px;
          color: #fff;
          font-size: 10px;
        }

        .card-panel-num {
          display: block;
          font-size: 20px;
        
        }
      }
  }

  .card-panel-icon-wrapper {
    float: none !important;
    margin: 0 !important;

    svg {
      display: block;
      margin: 5px auto !important;
      float: none !important;
    }
  }
}

.gr1{
  background: rgb(33,147,176);
  background: linear-gradient(60deg, rgba(33,147,176,1) 50%, rgba(109,213,237,1) 100%);
}

.gr2{
  background: rgb(204,43,94);
  background: linear-gradient(60deg, rgba(204,43,94,1) 34%, rgba(117,58,136,1) 86%);
}

.gr3{
  background: rgb(66,39,90);
  background: linear-gradient(60deg, rgba(66,39,90,1) 34%, rgba(115,75,109,1) 86%);
}

.gr4{
  background: rgb(44,62,80);
  background: linear-gradient(60deg, rgba(44,62,80,1) 39%, rgba(189,195,199,1) 100%);
}

.gr5{
  background: rgb(222,98,98);
  background: linear-gradient(60deg, rgba(222,98,98,1) 39%, rgba(255,184,140,1) 100%);
}

.gr6{
  background: rgb(72,177,191);
  background: linear-gradient(60deg, rgba(72,177,191,1) 50%, rgba(6,190,182,1) 69%);
}

.gr7{
  background: rgb(221,67,86);
  background: linear-gradient(60deg, rgba(221,67,86,1) 40%, rgba(244,92,67,1) 81%);
}

.gr8{
  background: rgb(97,67,133);
  background: linear-gradient(60deg, rgba(97,67,133,1) 40%, rgba(81,99,149,1) 81%);
}

.gr9{
  background: rgb(239,98,159);
  background: linear-gradient(60deg, rgba(239,98,159,1) 40%, rgba(238,205,163,1) 81%);
}

.gr10{
  background: rgb(2,170,176);
  background: linear-gradient(60deg, rgba(2,170,176,1) 57%, rgba(0,205,172,1) 81%);
}

.gr11{
  background: rgb(0,4,40);
  background: linear-gradient(60deg, rgba(0,4,40,1) 0%, rgba(0,78,146,1) 77%);
}

.gr12{
  background: rgb(123,67,151);
  background: linear-gradient(60deg, rgba(123,67,151,1) 0%, rgba(220,36,48,1) 77%);
}

.gr13{
  background: rgb(24,90,157);
  background: linear-gradient(60deg, rgba(24,90,157,1) 0%, rgba(67,206,162,1) 77%);
}

.gr14{
  background: rgb(69,104,220);
  background: linear-gradient(60deg, rgba(69,104,220,1) 0%, rgba(176,106,179,1) 77%);
}

.gr15{
  background: rgb(25,84,123);
  background: linear-gradient(60deg, rgba(25,84,123,1) 25%, rgba(255,216,155,1) 87%);
}

.gr16{
  background: rgb(58,28,113);
  background: linear-gradient(60deg, rgba(58,28,113,1) 0%, rgba(215,109,119,1) 71%, rgba(255,175,123,1) 96%);
}

.gr17{
  background: rgb(54,209,220);
  background: linear-gradient(60deg, rgba(54,209,220,1) 0%, rgba(91,134,229,1) 71%);
}

.gr18{
  background: rgb(195,55,100);
  background: linear-gradient(60deg, rgba(195,55,100,1) 0%, rgba(29,38,113,1) 71%);
}

.gr19{
  background: rgb(170,7,107);
  background: linear-gradient(60deg, rgba(170,7,107,1) 0%, rgba(97,4,95,1) 71%);
}

.gr20{
  background: rgb(43,88,118);
  background: linear-gradient(60deg, rgba(43,88,118,1) 35%, rgba(78,67,118,1) 66%);
}

.user-profile {
  .user-name {  
    margin-bottom:5px;  
    font-size:25px;
    font-weight: bold;
  }
  .box-center {
    padding-top: 10px;
  }
  .user-role {
    padding-top: 0px;
    font-weight: 400;
    font-size: 14px;
  }
  .box-social {
    padding-top: 30px;
    .el-table {
      border-top: 1px solid #dfe6ec;
    }
  }
  .user-follow {
    padding-top: 20px;
  }
}
.user-avatar{
    width: 100px;
    height: 100px;
    margin-top: 10px;
    border-radius: 50px;
}


</style>

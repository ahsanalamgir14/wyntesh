<template>
  <div class="dashboard-editor-container">

    <el-row :gutter="10" class="panel-group">
      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <router-link to="/network/members">
          <div class="card-panel gr12">
            <div class="card-panel-icon-wrapper " >
              <i class="fas fa-users card-panel-icon"  ></i>
            </div>
            <div class="card-panel-description">
                <count-to :start-val="0" :end-val="dashboardStats.users" :duration="2600" class="card-panel-num" />
              <div class="card-panel-text">
                Total Members
              </div>
            </div>
          </div>
        </router-link>
      </el-col>
      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <router-link to="/network/members">
          <div class="card-panel gr4">
            <div class="card-panel-icon-wrapper icon-money">
              <i class="fas fa-user-times card-panel-icon"  ></i>
            </div>
            <div class="card-panel-description">
              
              <count-to :start-val="0" :end-val="dashboardStats.inactive_users" :duration="3200" class="card-panel-num" />
              <div class="card-panel-text">
                Inactive Members
              </div>
            </div>
          </div>
        </router-link>
      </el-col>
      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <router-link to="/wallet/withdrawal-requests">
          <div class="card-panel gr2" >
            <div class="card-panel-icon-wrapper icon-message">
              <i class="fas fa-wallet card-panel-icon"  ></i>
            </div>
            <div class="card-panel-description">
              
              <count-to :start-val="0" :end-val="parseInt(dashboardStats.wallet_balance)" :duration="3000" class="card-panel-num" />
              <div class="card-panel-text">
                Wallet Total
              </div>
            </div>
          </div>
        </router-link>
      </el-col>
      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <router-link to="reports//payout-monthwise">
          <div class="card-panel gr8" >
            <div class="card-panel-icon-wrapper icon-money">
              <i class="fas fa-rupee-sign card-panel-icon"  ></i>
            </div>
            <div class="card-panel-description">
              
              <count-to :start-val="0" :end-val="parseFloat(dashboardStats.total_payout)" :duration="3200" class="card-panel-num" />
              <div class="card-panel-text">
                Total Payout
              </div>
            </div>
          </div>
        </router-link>
      </el-col>
      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <router-link to="/shopping/orders/all">
          <div class="card-panel gr13" >
            <div class="card-panel-icon-wrapper icon-message">
              <i class="fas fa-shopping-cart  card-panel-icon"  ></i>
            </div>
            <div class="card-panel-description">
              
              <count-to :start-val="0" :end-val="dashboardStats.total_orders" :duration="3000" class="card-panel-num" />
              <div class="card-panel-text">
                Total Orders
              </div>
            </div>
          </div>
        </router-link>
      </el-col>

      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <router-link to="/reports/payout-monthwise">
          <div class="card-panel gr13" >
            <div class="card-panel-icon-wrapper icon-message">
              <i class="fas fa-shopping-cart  card-panel-icon"  ></i>
            </div>
            <div class="card-panel-description">
              
              <count-to :start-val="0" :end-val="dashboardStats.total_business_volume" :duration="3000" class="card-panel-num" />
              <div class="card-panel-text">
                Total BV
              </div>
            </div>
          </div>
        </router-link>
      </el-col>          
      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <router-link to="/wallet/withdrawal-requests">
          <div class="card-panel gr5" >
            <div class="card-panel-icon-wrapper icon-shopping">
              <i class="fas fa-upload card-panel-icon"  ></i>
            </div>
            <div class="card-panel-description">
              
              <count-to :start-val="0" :end-val="dashboardStats.pending_withdrawals" :duration="3600" class="card-panel-num" />
              <div class="card-panel-text">
                Pending Withdrawals
              </div>
            </div>
          </div>
        </router-link>
      </el-col>
      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <router-link to="/shopping/orders/new">
          <div class="card-panel gr6" >
            <div class="card-panel-icon-wrapper icon-shopping">
              <i class="fas fa-cart-arrow-down card-panel-icon"  ></i>
            </div>
            <div class="card-panel-description">
              
              <count-to :start-val="0" :end-val="dashboardStats.pending_orders" :duration="3600" class="card-panel-num" />
              <div class="card-panel-text">
                Pending Orders
              </div>
            </div>
          </div>
        </router-link>
      </el-col>
      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <router-link to="/kyc/pending">
          <div class="card-panel gr7" >
            <div class="card-panel-icon-wrapper icon-shopping">
              <i class="fas fa-images card-panel-icon"  ></i>
            </div>
            <div class="card-panel-description">
              
              <count-to :start-val="0" :end-val="dashboardStats.pending_kyc" :duration="3600" class="card-panel-num" />
              <div class="card-panel-text">
                Pending KYCs
              </div>
            </div>
          </div>
        </router-link>
      </el-col>
      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <router-link to="/support/tickets">
          <div class="card-panel gr14" >
            <div class="card-panel-icon-wrapper icon-shopping">
              <i class="fas fa-phone-square-alt card-panel-icon"  ></i>
            </div>
            <div class="card-panel-description">
              
              <count-to :start-val="0" :end-val="dashboardStats.tickets" :duration="3600" class="card-panel-num" />
              <div class="card-panel-text">
                Support Tickets
              </div>
            </div>
          </div>
        </router-link>
      </el-col>
      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <router-link to="/support/inquiries">
          <div class="card-panel gr8" >
            <div class="card-panel-icon-wrapper icon-shopping">
              <i class="fas fa-phone-square-alt card-panel-icon"  ></i>
            </div>
            <div class="card-panel-description">
              
              <count-to :start-val="0" :end-val="dashboardStats.inquiries" :duration="3600" class="card-panel-num" />
              <div class="card-panel-text">
                Inquiries
              </div>
            </div>
          </div>
        </router-link>
      </el-col>
    </el-row>

    <el-row :gutter="10">      
      <el-col :xs="24" :sm="24" :md="12" :lg="12">        
        <el-card shadow="never">
          <bar-chart :chartData="orderData" ></bar-chart>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="12" :lg="12">        
        <el-card shadow="never">
          <line-chart :chartData="activationData" ></line-chart>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="10" style="margin-top: 20px;">      
      <el-col :xs="24" :sm="24" :md="12" :lg="12">        
        <el-card shadow="never">
          <div slot="header" class="clearfix">
            <span>Monthly Member Joinings</span>            
          </div>
          <el-table
            :data="monthlyJoiningsCount"
            style="width: 100%">
            <el-table-column label="Month" min-width="150px">
              <template slot-scope="{row}">
                <span  >{{ row.month }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Joinings" min-width="150px">
              <template slot-scope="{row}">
                <span  >{{ row.count }}</span>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="12" :lg="12">        
        <el-card shadow="never">
          <div slot="header" class="clearfix">
            <span>Monthwise Business</span>            
          </div>
          <el-table
            :data="monthlyBusiness"
            style="width: 100%">
           <el-table-column label="Month" width="150px" align="center">
              <template slot-scope="{row}">
                <span>{{ row.sales_start_date | parseTime('{y}-{m}') }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Sales BV" width="130px" align="right">
              <template slot-scope="{row}">
                <span >{{ row.sales_bv }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Sales Amount" width="130px" align="right">
              <template slot-scope="{row}">
                <span >{{ row.sales_amount }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Total Payout" width="130px" align="right">
              <template slot-scope="{row}">
                <span >{{ row.total_payout }}</span>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-col>
    </el-row>

  </div>
</template>

<script>

import CountTo from 'vue-count-to'
import BarChart from './components/BarChart'
import LineChart from './components/LineChart'
import { dashboardStats,pastOrderStats,pastActivations,monthlyJoiningsCount,monthlyBusiness } from "@/api/admin/dashboard";

export default {
  name: 'DashboardAdmin',
  components: {
    CountTo,
    BarChart,
    LineChart
  },
  data() {
    return {
      dashboardStats:{},
      orderData:{},
      activationData:{},
      monthlyJoiningsCount:[],
      monthlyBusiness:[],
      barSum:[],
    }
  },
  async created() {
    this.getDashboardStats();
    await pastOrderStats().then(response => {
      this.orderData = { labels:response.orders.map(function (el) { return el.date; }), data:response.orders.map(function (el) { return el.sum; }), title:'Orders', color:'#e39c39' };
    });
    await pastActivations().then(response => {
      this.activationData = { labels:response.activations.map(function (el) { return el.date; }), data:response.activations.map(function (el) { return el.count; }), title:'Activations', color:'#60c402' };
    });

    await monthlyJoiningsCount().then(response => {
      this.monthlyJoiningsCount = response.data;
    });
    
    await monthlyBusiness().then(response => {
      this.monthlyBusiness = response.data;
    });

  },
  methods: {
    getDashboardStats(){
      dashboardStats().then(response => {
        this.dashboardStats = response.stats;
      });
    },

  }
}
</script>

<style lang="scss" scoped>
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
</style>

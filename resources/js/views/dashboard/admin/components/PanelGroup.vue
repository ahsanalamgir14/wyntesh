<template>
  <el-row :gutter="40" class="panel-group">
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel" @click="handleSetLineChartData('newVisitis')">
        <div class="card-panel-icon-wrapper " style="color: #F39C12;">
          <i class="fas fa-users card-panel-icon"  ></i>
        </div>
        <div class="card-panel-description">
            <count-to :start-val="0" :end-val="dashboardStats.users" :duration="2600" class="card-panel-num" />
          <div class="card-panel-text">
            Total Members
          </div>
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel" @click="handleSetLineChartData('messages')">
        <div class="card-panel-icon-wrapper icon-message">
          <i class="fas fa-user-check  card-panel-icon" style="color: #27AE60;" ></i>
        </div>
        <div class="card-panel-description">
          
          <count-to :start-val="0" :end-val="dashboardStats.courses" :duration="3000" class="card-panel-num" />
          <div class="card-panel-text">
            Paid Members
          </div>
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel" @click="handleSetLineChartData('purchases')">
        <div class="card-panel-icon-wrapper icon-money">
          <i class="fas fa-user-times card-panel-icon"  style="color: #EC7063;"></i>
        </div>
        <div class="card-panel-description">
          
          <count-to :start-val="0" :end-val="dashboardStats.topics" :duration="3200" class="card-panel-num" />
          <div class="card-panel-text">
            Blocked Members
          </div>
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel" @click="handleSetLineChartData('shoppings')">
        <div class="card-panel-icon-wrapper icon-shopping">
          <i class="fas fa-user-clock card-panel-icon"  style="color: #40c9c6;"></i>
        </div>
        <div class="card-panel-description">
          
          <count-to :start-val="0" :end-val="dashboardStats.quizzes" :duration="3600" class="card-panel-num" />
          <div class="card-panel-text">
            Todays Activations
          </div>
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel" @click="handleSetLineChartData('shoppings')">
        <div class="card-panel-icon-wrapper icon-shopping">
          <i class="fas fa-tag card-panel-icon"  style="color: #CB4335;"></i>
        </div>
        <div class="card-panel-description">
          
          <count-to :start-val="0" :end-val="dashboardStats.quizzes" :duration="3600" class="card-panel-num" />
          <div class="card-panel-text">
            Used Pins
          </div>
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel" @click="handleSetLineChartData('shoppings')">
        <div class="card-panel-icon-wrapper icon-shopping">
          <i class="fas fa-tag card-panel-icon"  style="color: #27AE60;"></i>
        </div>
        <div class="card-panel-description">
          
          <count-to :start-val="0" :end-val="dashboardStats.quizzes" :duration="3600" class="card-panel-num" />
          <div class="card-panel-text">
            Unused Pins
          </div>
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel" @click="handleSetLineChartData('shoppings')">
        <div class="card-panel-icon-wrapper icon-shopping">
          <i class="fas fa-images card-panel-icon"  style="color: #45B39D;"></i>
        </div>
        <div class="card-panel-description">
          
          <count-to :start-val="0" :end-val="dashboardStats.quizzes" :duration="3600" class="card-panel-num" />
          <div class="card-panel-text">
            Pending KYCs
          </div>
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel" @click="handleSetLineChartData('shoppings')">
        <div class="card-panel-icon-wrapper icon-shopping">
          <i class="fas fa-phone-square-alt card-panel-icon"  style="color: #F5B041;"></i>
        </div>
        <div class="card-panel-description">
          
          <count-to :start-val="0" :end-val="dashboardStats.quizzes" :duration="3600" class="card-panel-num" />
          <div class="card-panel-text">
            Inquiries
          </div>
        </div>
      </div>
    </el-col>
  </el-row>
</template>

<script>
import CountTo from 'vue-count-to'
import {
  dashboardStats
} from "@/api/admin/dashboard";

export default {
  components: {
    CountTo
  },
  created() {
    this.getDashboardStats();
  },
   data() {
    return {
      dashboardStats:{},
    }
  },
  methods: {
    getDashboardStats(){
      dashboardStats().then(response => {
        this.dashboardStats = response.stats;
      });
    },
    handleSetLineChartData(type) {
      this.$emit('handleSetLineChartData', type)
    }
  }
}
</script>

<style lang="scss" scoped>

.panel-group {
  margin-top: 18px;

  .card-panel-col {
    margin-bottom: 32px;
  }

  .card-panel {
    height: 108px;
    cursor: pointer;
    font-size: 12px;
    position: relative;
    overflow: hidden;
    color: #666;
    background: #fff;
    box-shadow: 4px 4px 40px rgba(0, 0, 0, .05);

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
          color: rgba(0, 0, 0, 0.45);
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
          color: rgba(0, 0, 0, 0.45);
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
</style>

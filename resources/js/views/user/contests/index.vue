<template>
  <div class="app-container">
    <div style="display:block">
      <h1 class="text-3xl mb-2 text-center">{{contest.name}}</h1>
      <vue-countdown-timer
      class="text-center mb-4"
      :start-time="'2018-10-10 00:00:00'"
      :end-time="parseTime(contest.end_date,'{y}-{m}-{d}')"
      :interval="1000"
      :start-label="'Until start:'"
      :end-label="'Contest ends in'"
      label-position="begin"
      :end-text="'Contest ended!'"
      :day-txt="'days'"
      :hour-txt="'hours'"
      :minutes-txt="'minutes'"
      :seconds-txt="'seconds'">
      <template slot="start-label" slot-scope="scope">
        <span style="color: red" v-if="scope.props.startLabel !== '' && scope.props.tips && scope.props.labelPosition === 'begin'">{{scope.props.startLabel}}:</span>
        <span style="color: blue" v-if="scope.props.endLabel !== '' && !scope.props.tips && scope.props.labelPosition === 'begin'">{{scope.props.endLabel}}</span>
      </template>
 
      <template slot="countdown" slot-scope="scope">
        <br>
        <span class="text-xl text-red-500">{{scope.props.days}} </span><i>{{scope.props.dayTxt}}</i>
        <span class="text-xl text-red-500">{{scope.props.hours}} </span><i>{{scope.props.hourTxt}}</i>
        <span class="text-xl text-red-500">{{scope.props.minutes}} </span><i>{{scope.props.minutesTxt}}</i>
        <span class="text-xl text-red-500">{{scope.props.seconds}} </span><i>{{scope.props.secondsTxt}}</i>
      </template>
 
      <template slot="end-text" slot-scope="scope">
        <span style="color: green">{{ scope.props.endText}}</span>
      </template>
    </vue-countdown-timer>
    </div>
    <div v-if="contest.image" style="display:block">
      <img :src="contest.image" style="margin:0 auto;">
    </div>
    <el-row>
      <el-col>
        <el-tabs v-model="currentTab" @tab-click="tabChange" class="mt-4">
          <el-tab-pane label="Afiiliate" name="affiliate">

          </el-tab-pane>
          <el-tab-pane label="Rising Affiliate" name="rising-affiliate">
            
          </el-tab-pane>
          <el-tab-pane label="Team Affiliate" name="team-affiliate">
            
          </el-tab-pane>
          <el-tab-pane label="Special Awards" name="awards">
            
          </el-tab-pane>
        </el-tabs>
      </el-col>
    </el-row>
    <div class="filter-container">
      <el-input v-model="listQuery.search" placeholder="Member Id" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">Search</el-button>
    </div>
    <div :class="{'hidden':hidden}" class="pagination-container">
      <el-pagination :current-page.sync="listQuery.page" :page-size.sync="pageSize" :layout="layout" :page-sizes="pageSizes" :total="this.total" @current-change="handleCurrentChange" />
    </div>
  </div>
</template>
<script>
  import { getCurrentContest,getContestStats,getSpecialAwards } from "@/api/user/contests";
import crown from '@/assets/images/crown.png'
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import { scrollTo } from '@/utils/scroll-to';

export default {
  name: "Payouts",
  directives: { waves },
  data() {
    return {
      is_mobile:false,
      crown: crown,
      hidden: false,
      pageSize: 10,
      layout: 'total, sizes, prev,next, jumper',
      pageSizes: [5,10, 15, 20, 30, 50,500,5000],
      list: [],
      specialAwards: [],
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 10,
        search: undefined,
        rank_id: 1,
        sort: "+id"
      },
      currentTab:'affiliate',
      contest:{
        name:undefined,
        description:undefined,
        image:undefined,
      },
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    this.getContest();
    if(window.screen.width <= '550'){
      this.is_mobile=true;
    }
  },
  methods: {
    parseTime,
    getList() {
      getContestStats(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;        
      });
    },
    getSpecialAwards(){
      getSpecialAwards(this.listQuery).then(response => {
        this.specialAwards = response.data.data;
        this.total = response.data.total;        
      });
    },
    getContest(){
      getCurrentContest().then(response => {
        this.contest = response.data;      
      });
    },
    handleCurrentChange(val) {
        this.getList();
    },
    tabChange(tab, event){
      if(tab.name=='affiliate'){
        this.listQuery.rank_id=1;
        this.getList();
      }else if(tab.name=='rising-affiliate'){
        this.listQuery.rank_id=11;        
        this.getList();
      }else if(tab.name=='team-affiliate'){
        this.listQuery.rank_id=2;                
        this.getList();
      }else if(tab.name=='awards'){
        this.getSpecialAwards();
      }
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
  }
};
</script>
<style scoped type="css">
</style>

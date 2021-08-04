<template>
  <div class="app-container" v-bind:style="{ backgroundImage: 'url(' + bg1 + ')' }" style="background-size: cover;padding: 0;">
    <div v-if="contest!=null">
      <div style="display:block">
        <!-- <div v-if="contest.image" style="display:block">
          <img :src="contest.image" style="margin:0 auto;">
        </div> -->
        <!-- <h1 class="text-xl pt-6 mb-2 text-white text-center">{{contest.name}}</h1> -->
        <vue-countdown-timer class="text-center mb-4 pt-4" :start-time="'2018-10-10 00:00:00'" :end-time="parseTime(contest.end_date,'{y}-{m}-{d} {h}:{i}:{s}')" :interval="1000" :start-label="'Until start:'" :end-label="'To go...'" label-position="end" :end-text="'Contest ended!'" :day-txt="'days'" :hour-txt="'hours'" :minutes-txt="'minutes'" :seconds-txt="'seconds'">
          <template slot="start-label" slot-scope="scope">
            <span style="color: red" v-if="scope.props.startLabel !== '' && scope.props.tips && scope.props.labelPosition === 'begin'">{{scope.props.startLabel}}:</span>
            <span style="color: white" v-if="scope.props.endLabel !== '' && !scope.props.tips && scope.props.labelPosition === 'begin'">{{scope.props.endLabel}}</span>
          </template>
          <template slot="countdown" slot-scope="scope">
            <span class="text-2xl " style="color:#FFD700">{{scope.props.days}} </span><i style="color:#FFD700">{{scope.props.dayTxt}}</i>
            <span class="text-2xl " style="color:#FFD700">{{scope.props.hours}} </span><i style="color:#FFD700">{{scope.props.hourTxt}}</i>
            <span class="text-2xl " style="color:#FFD700">{{scope.props.minutes}} </span><i style="color:#FFD700">{{scope.props.minutesTxt}}</i>
            <span class="text-2xl " style="color:#FFD700">{{scope.props.seconds}} </span><i style="color:#FFD700">{{scope.props.secondsTxt}}</i>
            <br>
          </template>
          <template slot="end-label" slot-scope="scope">
            <span style="color: red" v-if="scope.props.startLabel !== '' && scope.props.tips && scope.props.labelPosition === 'end'">{{scope.props.startLabel}}:</span>
            <span style="color: white" v-if="scope.props.endLabel !== '' && !scope.props.tips && scope.props.labelPosition === 'end'">{{scope.props.endLabel}}</span>
          </template>
          <template slot="end-text" slot-scope="scope">
            <span style="color: green">{{ scope.props.endText}}</span>
          </template>
        </vue-countdown-timer>
      </div>
      <div v-if="contest.image" style="display:block">
        <!-- <img :src="contest.image" style="margin:0 auto;"> -->
      </div>
      <div>
        <el-row>
          <el-col>
            <el-tabs v-model="currentTab" @tab-click="tabChange" class="mt-4">
              <el-tab-pane label="Affiliate" name="affiliate">
                <section class="flex items-center justify-center  pb-4">
                  <div class="container">
                    <div>
                      <div class="text-left flex bg-red font-medium md:text-2xl px-3 py-4 rounded-full" style="background: #ddddddc7;color: #000;">
                        <div class="relative w-1/2 md:w-1/2 px-0 md:px-4 self-center text-sm md:text-xl lg:text-xl"> Position </div>
                        <div class="relative w-full px-4 self-start text-sm md:text-xl lg:text-xl"> Name </div>
                        <div class="relative w-1/2  px-4 self-center text-sm md:text-xl lg:text-xl">Points</div>
                      </div>
                      <div v-for=" (item,index) in list">
                        <div class="text-left flex px-3 py-4 rounded-full mt-1" style="background: linear-gradient(#03256C, #1768AC);color: #fff;" v-if="index==0">
                          <div class="relative  w-1/2 md:w-1/2 px-0 md:px-4 self-center">
                            <img class="w-12 md:w-16" :src="award1" alt="">
                          </div>
                          <div class="flex relative w-full px-4 items-center">
                            <pan-thumb :image="item.member.user.profile_picture?item.member.user.profile_picture:'/images/avatar.png'" :height="'60px'" :width="'60px'" :hoverable="false" />
                            <span class="pl-3 text-xs md:text-lg lg:text-lg md:font-semibold"> {{item.member.user.name}}
                              <div class="block"> {{item.member.kyc.city}} </div>
                            </span>
                          </div>
                          <div class="relative w-1/2  px-4 self-center text-xs md:text-lg lg:text-lg md:font-semibold">{{parseInt(item.points)}}</div>
                        </div>
                        <div class="text-left flex px-3 py-4 rounded-full mt-1" style="background: linear-gradient(#03256C, #1768AC);color: #fff;" v-if="index==1">
                          <div class="relative  w-1/2 md:w-1/2 px-0 md:px-4 self-center">
                            <img class="w-12 md:w-16" :src="award2" alt="">
                          </div>
                          <div class="flex relative w-full px-4 items-center">
                            <pan-thumb :image="item.member.user.profile_picture?item.member.user.profile_picture:'/images/avatar.png'" :height="'60px'" :width="'60px'" :hoverable="false" />
                            <span class="pl-3 text-xs md:text-lg lg:text-lg md:font-semibold"> {{item.member.user.name}}
                              <div class="block"> {{item.member.kyc.city}} </div>
                            </span>
                          </div>
                          <div class="relative w-1/2  px-4 self-center text-xs md:text-lg lg:text-lg md:font-semibold">{{parseInt(item.points)}}</div>
                        </div>
                        <div class="text-left flex px-3 py-4 rounded-full mt-1" style="background: linear-gradient(#03256C, #1768AC);color: #fff;" v-if="index==2">
                          <div class="relative  w-1/2 md:w-1/2 px-0 md:px-4 self-center">
                            <img class="w-12 md:w-16" :src="award3" alt="">
                          </div>
                          <div class="flex relative w-full px-4 items-center">
                            <pan-thumb :image="item.member.user.profile_picture?item.member.user.profile_picture:'/images/avatar.png'" :height="'60px'" :width="'60px'" :hoverable="false" />
                            <span class="pl-3 text-xs md:text-lg lg:text-lg md:font-semibold"> {{item.member.user.name}}
                              <div class="block"> {{item.member.kyc.city}} </div>
                            </span>
                          </div>
                          <div class="relative w-1/2  px-4 self-center text-xs md:text-lg lg:text-lg md:font-semibold">{{parseInt(item.points)}}</div>
                        </div>
                        <div class="text-left flex px-3 py-4 rounded-full mt-1" style="linear-gradient(#f02ec200, #6094ea33);color: #fff;border: 1px solid #fff;" v-if="index>2">
                          <div class="relative text-3xl w-1/2 md:w-1/2 px-0 md:px-4 self-center">
                            <span style="padding-left: 25px;">{{index+1}}</span>
                          </div>
                          <div class="flex relative w-full px-4 items-center">
                            <pan-thumb :image="item.member.user.profile_picture?item.member.user.profile_picture:'/images/avatar.png'" :height="'60px'" :width="'60px'" :hoverable="false" />
                            <span class="pl-3 text-xs md:text-lg lg:text-lg md:font-semibold"> {{item.member.user.name}}
                              <div class="block"> {{item.member.kyc.city}} </div>
                            </span>
                          </div>
                          <div class="relative w-1/2  px-4 self-center text-xs md:text-lg lg:text-lg md:font-semibold">{{parseInt(item.points)}}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </section>
              </el-tab-pane>
              <el-tab-pane label="Rising Affiliate" name="rising-affiliate">
                <div>
                  <div class="text-left flex bg-red font-medium md:text-2xl px-3 py-4 rounded-full" style="background: #ddddddc7;color: #000;">
                    <div class="relative w-1/2 md:w-1/2 px-0 md:px-4 self-center text-sm md:text-xl lg:text-xl"> Position </div>
                    <div class="relative w-full px-4 self-start text-sm md:text-xl lg:text-xl"> Name </div>
                    <div class="relative w-1/2  px-4 self-center text-sm md:text-xl lg:text-xl">Points</div>
                  </div>
                  <div v-for=" (item,index) in list">
                    <div class="text-left flex px-3 py-4 rounded-full mt-1" style="background: linear-gradient(#03256C, #1768AC);color: #fff;" v-if="index==0">
                      <div class="relative  w-1/2 md:w-1/2 px-0 md:px-4 self-center">
                        <img class="w-12 md:w-16" :src="award1" alt="">
                      </div>
                      <div class="flex relative w-full px-4 items-center">
                        <pan-thumb :image="item.member.user.profile_picture?item.member.user.profile_picture:'/images/avatar.png'" :height="'60px'" :width="'60px'" :hoverable="false" />
                        <span class="pl-3 text-xs md:text-lg lg:text-lg md:font-semibold"> {{item.member.user.name}}
                          <div class="block"> {{item.member.kyc.city}} </div>
                        </span>
                      </div>
                      <div class="relative w-1/2  px-4 self-center text-xs md:text-lg lg:text-lg md:font-semibold">{{parseInt(item.points)}}</div>
                    </div>
                    <div class="text-left flex px-3 py-4 rounded-full mt-1" style="background: linear-gradient(#03256C, #1768AC);color: #fff;" v-if="index==1">
                      <div class="relative  w-1/2 md:w-1/2 px-0 md:px-4 self-center">
                        <img class="w-12 md:w-16" :src="award2" alt="">
                      </div>
                      <div class="flex relative w-full px-4 items-center">
                        <pan-thumb :image="item.member.user.profile_picture?item.member.user.profile_picture:'/images/avatar.png'" :height="'60px'" :width="'60px'" :hoverable="false" />
                        <span class="pl-3 text-xs md:text-lg lg:text-lg md:font-semibold"> {{item.member.user.name}}
                          <div class="block"> {{item.member.kyc.city}} </div>
                        </span>
                      </div>
                      <div class="relative w-1/2  px-4 self-center text-xs md:text-lg lg:text-lg md:font-semibold">{{parseInt(item.points)}}</div>
                    </div>
                    <div class="text-left flex px-3 py-4 rounded-full mt-1" style="background: linear-gradient(#03256C, #1768AC);color: #fff;" v-if="index==2">
                      <div class="relative  w-1/2 md:w-1/2 px-0 md:px-4 self-center">
                        <img class="w-12 md:w-16" :src="award3" alt="">
                      </div>
                      <div class="flex relative w-full px-4 items-center">
                        <pan-thumb :image="item.member.user.profile_picture?item.member.user.profile_picture:'/images/avatar.png'" :height="'60px'" :width="'60px'" :hoverable="false" />
                        <span class="pl-3 text-xs md:text-lg lg:text-lg md:font-semibold"> {{item.member.user.name}}
                          <div class="block"> {{item.member.kyc.city}} </div>
                        </span>
                      </div>
                      <div class="relative w-1/2  px-4 self-center text-xs md:text-lg lg:text-lg md:font-semibold">{{parseInt(item.points)}}</div>
                    </div>
                    <div class="text-left flex px-3 py-4 rounded-full mt-1" style="linear-gradient(#f02ec200, #6094ea33);color: #fff;border: 1px solid #fff;" v-if="index>2">
                      <div class="relative text-3xl w-1/2 md:w-1/2 px-0 md:px-4 self-center">
                        <span style="padding-left: 25px;">{{index+1}}</span>
                      </div>
                      <div class="flex relative w-full px-4 items-center">
                        <pan-thumb :image="item.member.user.profile_picture?item.member.user.profile_picture:'/images/avatar.png'" :height="'60px'" :width="'60px'" :hoverable="false" />
                        <span class="pl-3 text-xs md:text-lg lg:text-lg md:font-semibold"> {{item.member.user.name}}
                          <div class="block"> {{item.member.kyc.city}} </div>
                        </span>
                      </div>
                      <div class="relative w-1/2  px-4 self-center text-xs md:text-lg lg:text-lg md:font-semibold">{{parseInt(item.points)}}</div>
                    </div>
                  </div>
                </div>
              </el-tab-pane>
              <el-tab-pane label="Team Affiliate" name="team-affiliate">
                <div>
                  <div class="text-left flex bg-red font-medium md:text-2xl px-3 py-4 rounded-full" style="background: #ddddddc7;color: #000;">
                    <div class="relative w-1/2 md:w-1/2 px-0 md:px-4 self-center text-sm md:text-xl lg:text-xl"> Position </div>
                    <div class="relative w-full px-4 self-start text-sm md:text-xl lg:text-xl"> Name </div>
                    <div class="relative w-1/2  px-4 self-center text-sm md:text-xl lg:text-xl">Points</div>
                  </div>
                  <div v-for=" (item,index) in list">
                    <div class="text-left flex px-3 py-4 rounded-full mt-1" style="background: linear-gradient(#03256C, #1768AC);color: #fff;" v-if="index==0">
                      <div class="relative  w-1/2 md:w-1/2 px-0 md:px-4 self-center">
                        <img class="w-12 md:w-16" :src="award1" alt="">
                      </div>
                      <div class="flex relative w-full px-4 items-center">
                        <pan-thumb :image="item.member.user.profile_picture?item.member.user.profile_picture:'/images/avatar.png'" :height="'60px'" :width="'60px'" :hoverable="false" />
                        <span class="pl-3 text-xs md:text-lg lg:text-lg md:font-semibold"> {{item.member.user.name}}
                          <div class="block"> {{item.member.kyc.city}} </div>
                        </span>
                      </div>
                      <div class="relative w-1/2  px-4 self-center text-xs md:text-lg lg:text-lg md:font-semibold">{{parseInt(item.points)}}</div>
                    </div>
                    <div class="text-left flex px-3 py-4 rounded-full mt-1" style="background: linear-gradient(#03256C, #1768AC);color: #fff;" v-if="index==1">
                      <div class="relative  w-1/2 md:w-1/2 px-0 md:px-4 self-center">
                        <img class="w-12 md:w-16" :src="award2" alt="">
                      </div>
                      <div class="flex relative w-full px-4 items-center">
                        <pan-thumb :image="item.member.user.profile_picture?item.member.user.profile_picture:'/images/avatar.png'" :height="'60px'" :width="'60px'" :hoverable="false" />
                        <span class="pl-3 text-xs md:text-lg lg:text-lg md:font-semibold"> {{item.member.user.name}}
                          <div class="block"> {{item.member.kyc.city}} </div>
                        </span>
                      </div>
                      <div class="relative w-1/2  px-4 self-center text-xs md:text-lg lg:text-lg md:font-semibold">{{parseInt(item.points)}}</div>
                    </div>
                    <div class="text-left flex px-3 py-4 rounded-full mt-1" style="background: linear-gradient(#03256C, #1768AC);color: #fff;" v-if="index==2">
                      <div class="relative  w-1/2 md:w-1/2 px-0 md:px-4 self-center">
                        <img class="w-12 md:w-16" :src="award3" alt="">
                      </div>
                      <div class="flex relative w-full px-4 items-center">
                        <pan-thumb :image="item.member.user.profile_picture?item.member.user.profile_picture:'/images/avatar.png'" :height="'60px'" :width="'60px'" :hoverable="false" />
                        <span class="pl-3 text-xs md:text-lg lg:text-lg md:font-semibold"> {{item.member.user.name}}
                          <div class="block"> {{item.member.kyc.city}} </div>
                        </span>
                      </div>
                      <div class="relative w-1/2  px-4 self-center text-xs md:text-lg lg:text-lg md:font-semibold">{{parseInt(item.points)}}</div>
                    </div>
                    <div class="text-left flex px-3 py-4 rounded-full mt-1" style="linear-gradient(#f02ec200, #6094ea33);color: #fff;border: 1px solid #fff;" v-if="index>2">
                      <div class="relative text-3xl w-1/2 md:w-1/2 px-0 md:px-4 self-center">
                        <span style="padding-left: 25px;">{{index+1}}</span>
                      </div>
                      <div class="flex relative w-full px-4 items-center">
                        <pan-thumb :image="item.member.user.profile_picture?item.member.user.profile_picture:'/images/avatar.png'" :height="'60px'" :width="'60px'" :hoverable="false" />
                        <span class="pl-3 text-xs md:text-lg lg:text-lg md:font-semibold"> {{item.member.user.name}}
                          <div class="block"> {{item.member.kyc.city}} </div>
                        </span>
                      </div>
                      <div class="relative w-1/2  px-4 self-center text-xs md:text-lg lg:text-lg md:font-semibold">{{parseInt(item.points)}}</div>
                    </div>
                  </div>
                </div>
              </el-tab-pane>
              <el-tab-pane label="Special Awards" name="awards" v-if="contest.is_ended">
                <div>
                  <div class="text-left flex bg-red font-medium md:text-2xl px-3 py-4 rounded-full" style="background: #ddddddc7;color: #000;">
                    <div class="relative w-full md:w-1/2 px-0 md:px-4 self-center text-sm md:text-xl lg:text-xl"> Title </div>
                    <div class="relative w-full px-4 self-start text-sm md:text-xl lg:text-xl"> Name </div>
                  </div>
                  <div v-for=" (item,index) in specialAwards">
                    <div class="text-left flex px-3 py-4 rounded-full mt-1" style="background: linear-gradient(#03256C, #1768AC);color: #fff;">
                      <div class="relative text-md w-full md:w-1/2 px-0 md:px-4 self-center">
                        {{item.title}}
                      </div>
                      <div class="flex relative w-full px-4 items-center">
                        <pan-thumb :image="item.member.user.profile_picture?item.member.user.profile_picture:'/images/avatar.png'" :height="'60px'" :width="'60px'" :hoverable="false" />
                        <span class="pl-3 text-xs md:text-lg lg:text-lg md:font-semibold"> {{item.member.user.name}}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </el-tab-pane>
            </el-tabs>
          </el-col>
        </el-row>
      </div>
      <el-dialog width="40%" top="5px" height="700px" :visible.sync="dialogWinnerPupup">
        <div v-if="popup">
          <img :src="popup.image" max-height="500px;" />
        </div>
      </el-dialog>
      <div :class="{'hidden':hidden}" class="pagination-container">
        <el-pagination :current-page.sync="listQuery.page" :page-size.sync="pageSize" :layout="layout" :page-sizes="pageSizes" :total="this.total" @current-change="handleCurrentChange" />
      </div>
    </div>
    <div v-else>
      <h1 class="text-center text-white pt-44 pb-44 text-7xl">No Contest is running</h1>
    </div>
  </div>
</template>
<script>
  import { getCurrentContest,getContestStats,getSpecialAwards,getCurrentContestRankBanner } from "@/api/user/contests";
import crown from '@/assets/images/crown.png'
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import { scrollTo } from '@/utils/scroll-to';
import bg1 from '@/assets/images/bg-img.jpg'
import bg2 from '@/assets/images/bg-img-1.jpg'
import award1 from '@/assets/images/award-1.png'
import award2 from '@/assets/images/award-2.png'
import award3 from '@/assets/images/award-3.png'
import PanThumb from '@/components/PanThumb';

export default {
  name: "contest",
  directives: { waves },
  components: { PanThumb },
  data() {
    return {
      bg1,
      bg2,
      award1,
      award2,
      award3,
      is_mobile:false,
      crown: crown,
      hidden: true,
      pageSize: 5,
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
      popup:{
        image:null
      },
      currentTab:'affiliate',
      contest:{
        name:undefined,
        description:undefined,
        image:undefined,
      },
      dialogWinnerPupup: false,
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
      getCurrentContestRankBanner(this.listQuery).then(response => {
        if(response.data)
          this.popup.image=null;
        this.popup = response.data;
        if(this.popup){
          this.dialogWinnerPupup=true;
        }
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
        let data={
          rank_id:12,
          contest_id:this.listQuery.contest_id
        };
        getCurrentContestRankBanner(data).then(response => {
          if(response.data)
            this.popup.image=null;
          this.popup = response.data;
          if(this.popup){
            this.dialogWinnerPupup=true;
          }
        });
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
  .el-tabs__item{
    color:#fff !important;
    font-size:20px;
  }
</style>

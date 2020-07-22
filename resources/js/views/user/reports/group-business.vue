<template>
  <div class="app-container">
    <!-- <div class="filter-container">
      <el-input
        v-model="listQuery.search"
        placeholder="Search Records"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >Search</el-button>
    </div> -->
    <el-row :gutter="20">    
      <el-form :model="carryForward" label-width="120px" label-position="top" size="mini">
        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
          <el-form-item label="Month">
              <el-input v-model="carryForward.payout.sales_start_date" disabled></el-input>
          </el-form-item>
        </el-col>
        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
          <el-form-item label="Leg">
            <el-input v-model="carryForward.position" disabled></el-input>
          </el-form-item>
        </el-col>
        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
          <el-form-item label="Carry Forwarded BV">
            <el-input v-model="carryForward.pv" disabled></el-input>
          </el-form-item>
        </el-col>
      </el-form>
    </el-row>
    <el-table
      :key="tableKey"
      v-loading="listLoading"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
    >
      <el-table-column label="Month" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }} </span>
        </template>
      </el-table-column>

      <el-table-column label="Group BV" min-width="150px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.totalPv }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Leg A" min-width="130px" align="right">
        <template slot-scope="{row}">
          <span v-if="row.position==1">{{ row.totalPv }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Leg B" min-width="130px" align="right">
        <template slot-scope="{row}">
          <span v-if="row.position==2">{{ row.totalPv }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Leg C" min-width="130px" align="right">
        <template slot-scope="{row}">
          <span v-if="row.position==3">{{ row.totalPv }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Leg D" min-width="130px" align="right">
        <template slot-scope="{row}">
          <span v-if="row.position==4">{{ row.totalPv }}</span>
        </template>
      </el-table-column>
      
    </el-table>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="listQuery.page"
      :limit.sync="listQuery.limit"
      @pagination="getList"
    />

  </div>
</template>

<script>
import { getGroupAndMatchingPvs, } from "@/api/user/payouts";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 

export default {
  name: "Payouts",
  components: { Pagination },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: "success",
        draft: "info",
        0: "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 10,
        search: undefined,
        sort: "-id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      carryForward:{
        payout:{},
      },
      pickerOptions: {
        shortcuts: [{
          text: 'Last week',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: 'Last month',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: 'Last 3 months',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
            picker.$emit('pick', [start, end]);
          }
        }]
      },
      dialogPayoutGenerateVisible:false,
      dialogStatus: "",
      dialogTitle:"",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
  }, 
  methods: { 
    getList() { 
      this.listLoading = true;
      getGroupAndMatchingPvs(this.listQuery).then(response => {
        this.list = response.data.data;
        if(Object.keys(response.data).length !== 0){
            this.total = response.total;
            // this.carryForward=response.carry_forward; 
            this.carryForward.payout.sales_start_date=response.data.dates;
            this.carryForward.position=response.data.position;
            this.carryForward.pv=response.data.pv;

            setTimeout(() => {
              this.listLoading = false;
            }, 1 * 100);
        }else{
              setTimeout(() => {
              this.listLoading = false;
            }, 1 * 100);
        }
      });
    },
    calculateGroupPV(legs){
      let group_pv=0;
      for (let key in legs) {
         group_pv+=parseFloat(legs[key].pv);
      }
      return Math.round(group_pv);
    },
    getPositionPV(legs,position){
      let pv=0;
      for (let key in legs) {
        if(legs[key].position==position)
          pv= legs[key].pv
      }
      return pv;
    },
    getMatched(legs){
      let pv=[];
      let group_pv=0;

      [0,1,2,3].forEach(function(key) {
        pv.push(parseFloat(legs[key]?legs[key].pv:0));
        group_pv+=parseFloat(legs[key]?legs[key].pv:0);
      });

      // for (let key in legs) {
      //    pv.push(parseFloat(legs[key].pv));
      //    group_pv+=parseFloat(legs[key].pv);
      // }
      pv.sort(function(a, b) {
        return a - b;
      });     
      return Math.round((group_pv-pv[3]));
    },
    getCarryForward(legs){
      let pv=[];
      let group_pv=0;

      [0,1,2,3].forEach(function(key) {
        pv.push(parseFloat(legs[key]?legs[key].pv:0));
        group_pv+=parseFloat(legs[key]?legs[key].pv:0);
      });

      pv.sort(function(a, b) {
        return a - b;
      });     
      return Math.round((pv[3]-pv[2]));
    },
    getCarryForwardLeg(legs){
      let pv=[];
      let group_pv=0;

      [0,1,2,3].forEach(function(key) {
        pv.push(parseFloat(legs[key]?legs[key].pv:0));
        group_pv+=parseFloat(legs[key]?legs[key].pv:0);
      });
      
      let carry_index=pv.indexOf(Math.max.apply(window,pv));
      if(carry_index==0){
        return 'A';
      }else if(carry_index==1){
        return 'B';
      }else if(carry_index==2){
        return 'C';
      }else if(carry_index==3){
        return 'D';
      }
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    sortChange(data) {
      const { prop, order } = data;
      if (prop === "id") {
        this.sortByID(order);
      }
    },
    sortByID(order) {
      if (order === "ascending") {
        this.listQuery.sort = "+id";
      } else {
        this.listQuery.sort = "-id";
      }
      this.handleFilter();
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
    }
  }
};
</script>

<style scoped>
.el-drawer__body {
  padding: 20px;
}
.pagination-container {
  margin-top: 5px;
}
.pagination-container {
  background: #fff;
  padding: 15px 16px;
}

</style>

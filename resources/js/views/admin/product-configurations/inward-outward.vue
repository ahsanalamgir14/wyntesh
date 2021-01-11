<template>
  <div class="app-container">
    <div class="filter-container">
      <el-select v-model="listQuery.inwardOutwordFlag" size="mini" @change="handleFilter" clearable class="filter-item mobile_class" style="width:200px;" filterable placeholder="Inward/outward">
        <el-option label="Inward" value="Inward"></el-option>
        <el-option label="Outward" value="Outward"></el-option>
      </el-select>
      <br>
      <el-input v-model="listQuery.search" placeholder="Search Records" style="width: 200px;" class="filter-item mobile_class" size="mini" @keyup.enter.native="handleFilter" />
      <el-button v-waves size="mini" class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">Search</el-button>
      <el-button size="mini" class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">Add</el-button>
    </div>
    <el-table :key="tableKey" v-loading="listLoading" :data="list" border fit highlight-current-row style="width: 100%;" @sort-change="sortChange">
      <el-table-column label="ID" prop="id" sortable="custom" align="center" width="80" :class-name="getSortClass('id')" :index="indexMethod">
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Product" width="170px">
        <template slot-scope="{row}">
          <span>{{ row.product.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Variant" width="130px">
        <template slot-scope="{row}">
          <span>{{ (row.variant.color?row.variant.color.name:'') +" - "+ (row.variant.size?row.variant.size.brand_size:'') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="SKU" width="200px">
        <template slot-scope="{row}">
          <span>{{ row.sku }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Order No" width="120"  align="center">
        <template slot-scope="{row}">
          <span>{{ row.order?row.order.order_no:'' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Units Inward" width="120px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.units_inward }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Units Outward" width="120px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.units_outward }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Inward Challan No." width="170px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.inward_challan_number }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Outward Challan No." width="170px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.outward_challan_number }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Files" align="center" width="150" class-name="small-padding">
        <template slot-scope="{row}" v-if="row.files.length>0">
          <el-tooltip content="Attachment" placement="right" effect="dark">
            <el-button type="success" icon="el-icon-files" circle :loading="buttonLoading" @click="logFileImage(row)"></el-button>
          </el-tooltip>
        </template>
      </el-table-column>
      <el-table-column label="IS Order Placed?" width="150px"align="center">
        <template slot-scope="{row}">
          <span>{{ row.is_order_placed?"Yes":"No" }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Is Order Returned" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.is_order_returned?"Yes":"No" }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Note" width="150px">
        <template slot-scope="{row}">
          <span>{{ row.note }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Created At" width="170px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{d}-{m}-{y} {h}:{i}:{s}') }}</span>
        </template>
      </el-table-column>
    </el-table>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
    <el-dialog title="Files" width="85%" top="30px" :visible.sync="dailoglogsImages">
      <el-row :gutter="10">
        <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6" v-for="items in logFiles" :key="items.key">
          <div class="img-upload">
            <div v-if="items.type == 'image/jpeg' ">
              <img v-if="items.url" :src="items?items.url:''" class="avatar" style="margin-bottom: 10px !important;margin-top: 20px !important;">
              <a :href="'/api/download-file?file='+items.url" class="link-black text-sm">
                <i class="el-icon-download" /> Download Attachment
              </a>
            </div>
            <div v-else>
              <img v-if="items.url" :src="documentImg" class="avatar" style="margin-bottom: 67px !important;width: 200px !important;height: 139px !important;">
              <a :href="'/api/download-file?file='+items.url" class="link-black text-sm">
                <i class="el-icon-download" /> Download Attachment
              </a>
            </div>
          </div>
        </el-col>
      </el-row>
    </el-dialog>
    <el-dialog title="Add Inward/Outward" :fullscreen="false" width="80%" top="2vh" :visible.sync="dialogSizeVisible">
      <el-form ref="addStockForm" :rules="rules" :model="temp">
        <el-row :gutter="30">
          <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
            <el-form-item label="Inward Outward" prop="inwardoutward">
              <el-select @change="getInOutWard($event)" v-model="temp.inwardoutward" clearable style="width:100%;" filterable placeholder="Inward Outward">
                <el-option v-for="item in inwardOutward" :key="item.key" :label="item.lable" :value="item.key">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="SKU" prop="sku">
              <el-select @change="getSkuInfo($event)" filterable clearable style="width:100%;" v-model="temp.sku">
                <el-option v-for="item in productVariant" :key="item.id" :label="item.sku_code" :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="Product" prop="product_id">
              <el-input disabled v-model="temp.product_id" />
            </el-form-item>
            <el-form-item label="Variant" prop="variant_id">
              <el-input disabled v-model="temp.variant_id" />
            </el-form-item>
            <el-form-item label="Current Stock" prop="stock">
              <el-input disabled v-model="temp.stock" />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
            <el-form-item label="Inward Challan Number" prop="inward_challan_number" v-if="flagInwardChalan">
              <el-input v-model="temp.inward_challan_number" />
            </el-form-item>
            <el-form-item label="Units Inward" prop="units_inward" v-if="flagInward">
              <el-input type="number" v-model="temp.units_inward" />
            </el-form-item>
            <el-form-item label="Outward Challan Number" prop="outward_challan_number" v-if="flagOutwardChalan">
              <el-input v-model="temp.outward_challan_number" />
            </el-form-item>
            <el-form-item label="Units Outward" prop="units_outward" v-if="flagOutward">
              <el-input type="number" v-model="temp.units_outward" />
            </el-form-item>
            <el-form-item label="Note" prop="note">
              <el-input type="textarea" v-model="temp.note" />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
            <el-form-item prop="pan_image">
              <label for="file">Attachments</label>
              <el-upload ref="upload" class="avatar-uploader" multiple action="#" :show-file-list="true" :auto-upload="false" :on-change="handleChequeChange" :on-remove="handleChequeRemove" :limit="10" :file-list="StockFileList" accept="">
                <i v-if="temp.cheque_image" slot="default" class="el-icon-plus" />
                <i v-else class="el-icon-plus avatar-uploader-icon" />
              </el-upload>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogSizeVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
          Save
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import {
  fetchProductVariant,
  fetchList,
  addStock,
} from "@/api/admin/inward-outward";
import { parse_currency } from "@/utils/currencies";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";
import role from '@/directive/role'; // Permission directive (v-role)
import documentImg from '@/assets/images/document.png'
export default {
  name: "Packages",
  components: { Pagination },
  directives: { waves, role },
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
      documentImg: documentImg,
      kycsTabs: 'logImage',
      logFiles: [],
      flagOutward: false,
      flagInward: false,
      flagInwardChalan: false,
      flagOutwardChalan: false,
      StockFile: [],
      StockFileList: [],
      tableKey: 0,
      list: [],
      total: 3,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 5,
        search: undefined,
        sort: "-id",
        inwardOutwordFlag: undefined,
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      inwardOutward: [
        { label: "Inward", key: "Inward" },
        { label: "Outward", key: "Outward" }
      ],
      temp: {
        inwardoutward: undefined,
        sku: undefined,
        product_id: undefined,
        variant_id: undefined,
        stock: undefined,
        inward_challan_number: undefined,
        inward_challan_number: undefined,
        outward_challan_number: undefined,
        units_inward: undefined,
        units_outward: undefined,
        note: undefined,
      },
      rules: {
        inwardoutward: [
          { required: true, message: "In OR Out ward is required", trigger: "blur" }
        ],
        sku: [
          { required: true, message: "SKU is required", trigger: "blur" }
        ],
        units_inward: [
          { required: true, message: "Units inward required", trigger: "blur" }
        ],
        units_outward: [
          { required: true, message: "Units Outward required", trigger: "blur" }
        ],
      },
      dialogSizeVisible: false,
      dailoglogsImages: false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      fileList: [],
      productVariant: undefined,
      file: undefined,
      downloadLoading: false,
      buttonLoading: false,

    };
  },
  created() {
    this.getList();
    this.getSKU();
  },
  methods: {
    handleChequeChange(file, fileList) {
      this.StockFile = fileList.slice(-100);
    },
    handleChequeRemove(file, fileList) {
      this.StockFile = [];
      this.StockFileList = [];
    },
    getList() {
      this.listLoading = true;
      fetchList(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    logFileImage(raw) {
      this.logFiles = raw.files;
      this.dailoglogsImages = true;
    },
    getSKU() {
      this.listLoading = true;
      fetchProductVariant(this.listQuery).then(response => {
        this.productVariant = response.data;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    resetTemp() {
      this.temp = {
        inwardoutward: undefined,
        sku: undefined,
        product_id: undefined,
        variant_id: undefined,
        stock: undefined,
        inward_challan_number: undefined,
        inward_challan_number: undefined,
        outward_challan_number: undefined,
        units_inward: undefined,
        units_outward: undefined,
        note: undefined,
      };
      this.StockFileList = [];
      this.file = undefined;
      this.fileList = [];
      this.flagInward = false;
      this.flagOutward = false;
      this.flagOutwardChalan = false;
      this.flagInwardChalan = false;
    },
    getSkuInfo(event) {
      let tmp = this.temp;
      this.productVariant.map(function(value, key) {
        if (value.id == event) {
          tmp.product_id = value.product.name;
          tmp.stock = value.stock;

          var size = "";
          var color = "";
          if (value.size) {
            size = value.size.name;
          }
          if (value.color) {
            color = value.color.name;
          }
          tmp.variant_id = size + '-' + color;
        }

      });
    },
    getInOutWard(event) {
      if (event == "Inward") {
        this.flagInward = true;
        this.flagInwardChalan = true;
        this.flagOutward = false;
        this.flagOutwardChalan = false;
      } else {
        this.flagInward = false;
        this.flagInwardChalan = false;
        this.flagOutward = true;
        this.flagOutwardChalan = true;
      }
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogSizeVisible = true;
      this.$nextTick(() => {
        this.$refs["addStockForm"].clearValidate();
      });
    },
    indexMethod(index) {
      let page = this.listQuery.page;
      alert(page)
      if (this.listQuery.page == 1) {
        let tempIndex = index * 1;
        let total = this.total + 1;
        return total - (tempIndex + 1);
      } else {

        let tempIndex = this.listQuery.limit * (page - 1) + index;;
        let total = this.total + 1;
        return total - (tempIndex + 1);
      }
    },
    createData() {
      this.$refs["addStockForm"].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          var form = new FormData();
          const form_data = this.temp;

          for (var key in form_data) {
            if (form_data[key] !== undefined && form_data[key] !== null) {
              form.append(key, form_data[key]);
            }
          }

          var files = [];
          this.StockFile.map(function(value, key) {
            form.append('image_' + key, value.raw);
          });

          addStock(form).then((response) => {
            this.getList();
            this.getSKU();
            this.dialogSizeVisible = false;
            this.buttonLoading = false;
            this.$notify({
              title: "Success",
              message: "Created Successfully",
              type: "success",
              duration: 2000
            })
          }).catch((res) => {
            this.buttonLoading = false;
          });
        }
      });
    },

    handleChange(f, fl) {
      if (fl.length > 1) {
        fl.shift();
      }
      this.file = f.raw;
    },
    handleRemove(file, fileList) {
      this.file = undefined;
      this.fileList = [];
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
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
      return sort === `+${key}` ?
        "ascending" :
        sort === `-${key}` ?
        "descending" :
        "";
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

.edit-input {
  padding-right: 100px;
}

.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}

</style>

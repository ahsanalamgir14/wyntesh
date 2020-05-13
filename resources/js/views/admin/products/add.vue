<template>
  <div class="app-container">
    <el-form ref="productForm" :rules="rules" :model="temp" label-position="top"  style="">
      <el-tabs type="border-card" v-loading="loading">
        <el-tab-pane label="Name and Pricing">          
          <el-row :gutter="20">
            <el-col  :xs="24" :sm="24" :md="12" :lg="9" :xl="9" >
              <el-form-item label="Product Number" prop="product_number">
                <el-input v-model="temp.product_number" />
              </el-form-item>
              <el-form-item label="Name" prop="name">
                <el-input v-model="temp.name" />
              </el-form-item>
              <el-form-item label="Brand Name" prop="brand_name">
                <el-input v-model="temp.brand_name" />
              </el-form-item>
              <el-form-item label="Quantity" prop="qty">
                <el-input type="number" min="1" v-model="temp.qty" />
              </el-form-item>
              <el-form-item label="Quantity Unit" prop="qty_unit">
                <el-input v-model="temp.qty_unit" />
              </el-form-item>
              
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="5" :xl="5" >
              <el-form-item label="GST %" prop="gst_rate">
                <el-input type="number" min="0" v-model="temp.gst_rate" />
              </el-form-item>
              <el-form-item label="Base Cost" prop="cost_base">
                <el-input type="number" @blur="calculateFinalCostPrice()" min="1" v-model="temp.cost_base" />
              </el-form-item>                
              <el-form-item label="GST on Base cost" prop="cost_gst">
                <el-input type="number" min="1" v-model="temp.cost_gst" />
              </el-form-item>
              <el-form-item label="Total Base cost" prop="cost_amount">
                <el-input type="number" min="1" v-model="temp.cost_amount" />
              </el-form-item>
              
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="5" :xl="5" >
              <el-form-item label="Stock" prop="stock">
                <el-input type="number" min="1" v-model="temp.stock" />
              </el-form-item> 
              <el-form-item label="DP Cost" prop="dp_base">
                <el-input type="number" min="1" @blur="calculateFinalDPPrice()" v-model="temp.dp_base" />
              </el-form-item>                
              <el-form-item label="GST on DP " prop="dp_gst">
                <el-input type="number" min="1" v-model="temp.dp_gst" />
              </el-form-item>
              <el-form-item label="Total DP" prop="dp_amount">
                <el-input type="number" min="1" v-model="temp.dp_amount" />
              </el-form-item>
              
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="5" :xl="5" >
              <el-form-item label="PV" prop="pv">
                <el-input type="number" min="0" v-model="temp.pv" />
              </el-form-item>
              <el-form-item label="Retail Cost" prop="retail_base">
                <el-input type="number" min="1" @blur="calculateFinalRetailPrice()" v-model="temp.retail_base" />
              </el-form-item>                
              <el-form-item label="GST on Retail price" prop="retail_gst">
                <el-input type="number" min="1" v-model="temp.retail_gst" />
              </el-form-item>
              <el-form-item label="Total Retail price" prop="retail_amount">
                <el-input type="number" min="1" v-model="temp.retail_amount" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row >
            <hr>
            <div style="float: right;margin-top:10px;">
              <el-button
                type="success"
                @click="is_updating?updateProduct():addProduct()"
              >Save</el-button>
            </div>
          </el-row>
        </el-tab-pane>
        <el-tab-pane label="Stock and Categories">
          <el-row :gutter="20">            
           
            <el-col  :xs="24" :sm="24" :md="12" :lg="8" :xl="8" >
               
                                        
              <el-form-item label="Discount Rate" prop="discount_rate">
                <el-input type="number" min="0" v-model="temp.discount_rate" />
              </el-form-item>
              <el-form-item label="Discount Amount" prop="discount_amount">
                <el-input type="number" min="0" v-model="temp.discount_amount" />
              </el-form-item>              
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="8" :xl="8" >
              <el-form-item label="Admin Fee" prop="admin_charge">
                <el-input type="number" min="0" v-model="temp.admin_fee" />
              </el-form-item>
              <el-form-item label="Shipping Charge" prop="shipping_fee">
                <el-input type="number" min="0" v-model="temp.shipping_fee" />
              </el-form-item>
              <el-form-item label="Categories" prop="parent_id">
                <el-select v-model="temp.categories" multiple clearable  style="width:100%;" filterable placeholder="Select Categories">
                  <el-option
                    v-for="item in categories"
                    :key="item.name"
                    :label="item.name"
                    :value="item.id">
                  </el-option>
                </el-select>
              </el-form-item>              
            </el-col>
            <el-col  :xs="24" :sm="12" :md="16" :lg="8" :xl="8">
              <div class="img-upload" >
                <el-form-item  prop="cover_image" style="float: right;margin-right: 40px;">
                      <label for="Cover Image" style="line-height: 2;"> Cover Image</label>
                      <el-upload
                        class="avatar-uploader"
                        action="#"
                         ref="upload"
                        :show-file-list="true"
                        :auto-upload="false"
                        :on-change="handleChange"
                        :on-remove="handleRemove"
                        :limit="1"
                        :file-list="fileList"
                        :on-exceed="handleExceed"
                        accept="image/png, image/jpeg">                      
                        <img v-if="temp.cover_image" :src="temp?temp.cover_image_thumbnail:''"  class="avatar">
                        <i v-if="temp.cover_image"  slot="default" class="el-icon-plus"></i>
                        <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                      </el-upload>
                      <a  v-if="temp.cover_image" :href="temp?temp.cover_image:''" target="_blank">View full image.</a>                      
                    </el-form-item>
              </div>
            </el-col>
          </el-row>
          <el-row >
            <hr>
            <div style="float: right;margin-top:10px;">
              <el-button
                type="success"
                @click="is_updating?updateProduct():addProduct()"
              >Save</el-button>
            </div>
          </el-row>
        </el-tab-pane>
        <el-tab-pane label="Details and Images">
          <el-row :gutter="50">            
            <el-col  :xs="24" :sm="24" :md="12" :lg="14" :xl="14" >
              <el-form-item  label="Description" prop="description">
                <tinymce v-model="temp.description"  :imageUploadButton="false" menubar="format" :toolbar="tools" id="productDescription" ref="productDescription" :value="temp.description" :height="50" />
              </el-form-item>
              <el-form-item  label="Benefits" prop="benefits">
                <tinymce v-model="temp.benefits"  :imageUploadButton="false" menubar="format" :toolbar="tools" id="productBenefits" ref="productBenefits" :value="temp.benefits" :height="50" />
              </el-form-item>          
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="10" :xl="10" v-if="is_updating">
                <div class="filter-container" style="margin-top: 10px;">
                  <span style="font-size: 14px; font-weight: bold; color: #606266;">Product Images</span>                  
                  <el-button
                    class="filter-item"
                    style="margin-left: 10px;float: right;"
                    size="mini"
                    type="success"
                    @click="handleAddImage()"
                  ><i class="fas fa-plus"></i> Add</el-button>
                </div>
                <el-table
                  :data="temp.images"
                  style="width: 100%">
                  <el-table-column label="Image" min-width="150px">
                    <template slot-scope="{row}">
                      <span ><a  v-if="row.url" :href="row.url" target="_blank">View image.</a></span>
                    </template>
                  </el-table-column>
                  <el-table-column label="Actions" align="center" width="200" class-name="small-padding">
                    <template slot-scope="{row}">                     
                      <el-button
                          circle
                          type="danger"
                          :loading="buttonLoading" 
                          icon="el-icon-delete"
                          @click="deleteImage(row)"
                          ></el-button>
                    </template>
                  </el-table-column>
                </el-table>
                                      
            </el-col>
          </el-row>
          <el-row >
            <hr>
            <div style="float: right;margin-top:10px;">
              <el-button
                type="success"
                @click="is_updating?updateProduct():addProduct()"
              >Save</el-button>
            </div>
          </el-row>
        </el-tab-pane>
      </el-tabs>
    </el-form>

    <el-dialog title="Add Image" width="30%" top="30px"  :visible.sync="dialogAddImageVisible">
      <el-form ref="imageForm"  style="">
        <el-row>
          <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
            <el-form-item  prop="cover_image" style="margin-right: 40px;">
              <label for="Select Image"> Select Image</label>
              <el-upload
                class="avatar-uploader"
                action="#"
                 ref="productImage"
                :show-file-list="true"
                :auto-upload="false"
                :on-change="handleImageChange"
                :on-remove="handleImageRemove"
                :limit="1"
                :file-list="imageFileList"
                :on-exceed="handleImageExceed"
                accept="image/png, image/jpeg">                                                     
                <i class="el-icon-plus avatar-uploader-icon"></i>
              </el-upload>
                                  
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogAddImageVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" :loading="buttonLoading" @click="addImage()">
          Upload
        </el-button>
      </div>
    </el-dialog>

  </div>
</template>

<script>
import {
  getAllCategories, createProduct, getProduct, updateProduct, deleteProductImage, uploadProductImage
} from "@/api/admin/products-and-categories";
import Tinymce from '@/components/Tinymce'
import waves from "@/directive/waves"; 
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "Settings",
  components: { Pagination,Tinymce },
  directives: { waves },
  data() {
    return {
      tools: [''],
      categories:[],
      fileList:[],
      file:undefined,
      imageFileList:[],
      imageFile:undefined,
      dialogAddImageVisible:false,
      temp: {
        product_number:undefined,
        name:undefined,
        brand_name:undefined,
        qty:undefined,
        qty_unit:undefined,
        description:undefined,
        benefits:undefined,
        gst_rate:undefined,
        cost_base:undefined,
        cost_gst:undefined,
        cost_amount:undefined,
        dp_base:undefined,
        dp_gst:undefined,        
        dp_amount:undefined,
        retail_base:undefined,
        retail_gst:undefined,
        retail_amount:undefined,
        discount_rate:undefined,
        discount_amount:undefined,
        admin_fee:undefined,
        shipping_fee:undefined,        
        pv:undefined,                
        stock:1,
        cover_image:undefined,
        cover_image_thumbnail:undefined,
        categories:[],
        images:[],
      },
      rules: {
        product_number: [
          { required: true, message: "Product Number is required.", trigger: "blur" }
        ],
        name: [
          { required: true, message: "Product name is required.", trigger: "blur" }
        ],
        brand_name: [
          { required: true, message: "Brand name is required", trigger: "blur" }
        ],
        qty: [
          { required: true, message: "Quantity is required", trigger: "blur" }
        ],
        qty_unit: [
          { required: true, message: "Quantity unit is required", trigger: "blur" }
        ],
        gst_rate: [
          { required: true, message: "GST rate is required", trigger: "blur" }
        ],
        cost_base: [
          { required: true, message: "Base cost is required.", trigger: "blur" }
        ],
        cost_gst: [
          { required: true, message: "GST on cost is required.", trigger: "blur" }
        ],
        cost_amount: [
          { required: true, message: "Final cost is required.", trigger: "blur" }
        ],
        dp_base: [
          { required: true, message: "DP Base price is required.", trigger: "blur" }
        ],
        dp_gst: [
          { required: true, message: "GST on dp price is required.", trigger: "blur" }
        ],
        dp_amount: [
          { required: true, message: "Final DP is required.", trigger: "blur" }
        ],
        retail_base: [
          { required: true, message: "Base retail price is required.", trigger: "blur" }
        ],
        retail_gst: [
          { required: true, message: "GST on retail price is required.", trigger: "blur" }
        ],
        retail_amount: [
          { required: true, message: "Final retail price is required.", trigger: "blur" }
        ],
        stock: [
          { required: true, message: "Stock quantity is required.", trigger: "blur" }
        ],
      },
      buttonLoading: false,
      loading:false,
      is_updating:false,
    };
  },
  created() {
    let id=this.$route.query.id
    if(id){
      this.is_updating=true;
      getProduct(id).then((response)=>{
        this.temp=response.data;
        var keys = [];
        response.data.categories.map(cat => {
            keys.push(cat.id);
        })
        keys = keys.filter((item, i, ar) => ar.indexOf(item) === i);
        this.temp.categories=keys;
      })
    }
    this.getAllCats();
  },
  methods: {
    getAllCats() {
      this.listLoading = true;
      getAllCategories().then(response => {
        this.categories = response.data;
      });
    },
    handleChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.file=f.raw      
    },
    handleRemove(file, fileList) {
       this.file=undefined;
       this.fileList=[];
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
    },
    handleImageChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.imageFile=f.raw      
    },
    handleImageRemove(file, fileList) {
       this.imageFile=undefined;
       this.imageFileList=[];
    },
    handleImageExceed(imageFile, imageFileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
    },
    calculateFinalCostPrice(){
      if(this.temp.gst_rate != undefined && this.temp.gst_rate != null){
        if(this.temp.gst_rate == 0){
          this.temp.cost_amount=0;
          this.temp.cost_amount=this.temp.cost_base;
        }else{
          let gst=(this.temp.gst_rate*this.temp.cost_base)/100;
          gst=Math.floor(gst);
          this.temp.cost_gst=gst;
          this.temp.cost_amount=parseInt(this.temp.cost_base)+gst;
        }        
      }
    },
    calculateFinalDPPrice(){
      if(this.temp.gst_rate != undefined && this.temp.gst_rate != null){
        if(this.temp.gst_rate == 0){
          this.temp.dp_amount=0;
          this.temp.dp_amount=this.temp.dp_base;
        }else{
          let gst=(this.temp.gst_rate*this.temp.dp_base)/100;
          gst=Math.floor(gst);
          this.temp.dp_gst=gst;
          this.temp.dp_amount=parseInt(this.temp.dp_base)+gst;
        }        
      }
    },
    calculateFinalRetailPrice(){
      if(this.temp.gst_rate != undefined && this.temp.gst_rate != null){
        if(this.temp.gst_rate == 0){
          this.temp.retail_amount=0;
          this.temp.retail_amount=this.temp.retail_base;
        }else{
          let gst=(this.temp.gst_rate*this.temp.retail_base)/100;
          gst=Math.floor(gst);
          this.temp.retail_gst=gst;
          this.temp.retail_amount=parseInt(this.temp.retail_base)+gst;
        }        
      }
    },
    addProduct() {
      this.$refs["productForm"].validate(valid => {
        if (valid) {
          this.loading=true;
          var form = new FormData();
          let form_data=this.temp;

          for ( var key in form_data ) {
            if(form_data[key] !== undefined && form_data[key] !== null){
              form.append(key, form_data[key]);
            }
          }

          form.append('file', this.file);

          createProduct(form).then((response) => {
            this.temp=response.data;
            this.loading=false;
            this.$router.push({ path: '/products/edit', query: { id: response.data.id } });
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            });
          });
        }
      });
    },
    updateProduct() {
      this.$refs["productForm"].validate(valid => {
        if (valid) {
          this.loading=true;
          var form = new FormData();
          let form_data=this.temp;

          for ( var key in form_data ) {
            if(form_data[key] !== undefined && form_data[key] !== null){
              form.append(key, form_data[key]);
            }
          }

          form.append('file', this.file);

          updateProduct(form).then((response) => {
            this.temp=response.data;
            this.fileList=[];
            this.loading=false;
            var keys = [];
            response.data.categories.map(cat => {
                keys.push(cat.id);
            })
            keys = keys.filter((item, i, ar) => ar.indexOf(item) === i);
            this.temp.categories=keys;
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            });
          });
        }
      });
    },
    handleAddImage(){
      this.dialogAddImageVisible=true;
      this.imageFileList=[];
    },
    addImage(){
      
      if(!this.imageFile){
          this.$message.error('Please select image');
          return;
      }
      var form = new FormData();
      form.append('id', this.temp.id);
      form.append('file', this.imageFile);
      this.buttonLoading=true;

      uploadProductImage(form).then((response) => {
        this.imageFileList=[];
        this.buttonLoading=false;
        this.dialogAddImageVisible=false;
        this.temp.images.push(response.data);
        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        });
      });

    },
    deleteImage(row){
      this.buttonLoading=true;
      deleteProductImage(row.id).then((data) => {
          this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
          });
          this.buttonLoading=false;
          const index = this.temp.images.indexOf(row);
          this.temp.images.splice(index, 1);
      });
    },
  }
};
</script>

<style scoped>

.edit-input {
  padding-right: 100px;
}

.el-form-item--medium .el-form-item__content, .el-form-item--medium .el-form-item__label {
    line-height: 0px;
}

.avatar {
    width: 200px;
    height: 115px;
    display: block;
  }

  .avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 200px;
    height: 115px !important;
    line-height: 115px;
    text-align: center;
  }

</style>

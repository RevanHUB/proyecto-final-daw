<script setup>
    import UserCardFavorite from "@/components/seguidos/userCardFavorites.vue";
    import InputSearch from "./inputSearchAdaptado.vue";
    import { searchByFilter } from "./followedSearchLogicAdaptado.js";
    import {usersAux, loading, followedListAux, fetchUsers} from "@/utils/followedSearchLogic.js";
    import { onMounted } from "vue";

    // onMounted(async() => {
    //   await fetchUsers();
    // });



    import commercesResults from "@/components/search/commercesResults.vue";
import contenedorSeguido from "@/components/perfiles/containers/contenedorSeguido.vue";
import comercios from "@/data/comerciosData";
import { getUserFollows } from "@/Api/perfiles/perfil.js";
import { setDefaultImgs } from "@/components/perfiles/helpers/defaultImgs";
import { ref } from "vue";
import { RouterLink } from "vue-router";

let followsList = ref(null); 
let filledFavoritos = ref([]);
async function responseCatcher(metodo, subRuta) {
  followsList.value = await getUserFollows(metodo, subRuta);
  console.log(followsList.value);
  followsList.value = setDefaultImgs(followsList.value);
  for(let i=0 ; i<followsList.value.length; i++){
    console.log(followsList.value[i].favorito)
    if(followsList.value[i].favorito){
      filledFavoritos.value.push(followsList.value[i])
    }
  }
}
responseCatcher("get", "/api/follows");
  // console.log(followedListAux)

  // function buscar(filter){
  //   followsList.value= searchByFilter(filter,followsList.value)
  // }
</script>

<template>
    <div class="flex flex-col">
      <!-- <InputSearch @searchByFilter="buscar"   />
      <div v-if="loading" >
        <p class="text-[#b9b9b9] mt-6 text-[1.5rem]">Cargando...</p>
      </div>
      <div v-else>
        <p class="text-sm text-[#b9b9b9] flex flex-start ml-6 m-2">Sigues a {{ followsList && followsList.length  }} cuentas</p>
        <div v-if="followsList.length === 0">
          <div class="p-2 leading-normal text-[#be9f73] bg-[#fdf9cf] opacity-70 rounded-lg mt-4 text-sm w-2/3 m-auto"  role="alert">
            <p>Sin coincidencias</p>
        </div>
      </div> -->
      <div>
        <div v-for="(user, index) in  filledFavoritos && filledFavoritos" :key="index">
          <UserCardFavorite
            :user="user"

          />
        </div>
      </div>

    </div>
  </template>

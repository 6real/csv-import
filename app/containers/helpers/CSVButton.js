import React, {useState} from 'react';
import {CSVReader} from 'react-papaparse'
import {Button} from '@wordpress/components';
import axios from "axios";

const CSVButton = () => {

    //State
    const [data, setData] = useState(false);
    const [posted, setPosted] = useState(false);
    const [loading, setLoading] = useState(false);

    //Little bit of style
    const msgValidation = {
        display: 'block',
        background: 'green',
        color: 'white',
        padding: '15px',
        fontSize: '18px',
        marginTop: '40px'
    };

    //Set the data to the state when is the csv is drop
    function handleOnDrop(data) {
        setData(data);
    }

    //Show error on the CSV
    function handleOnError(err) {
        console.error(err)
    }

    /**
     * Call Ajax for Post the data
     */

    async function post(data) {
        // eslint-disable-next-line no-undef
        const nonce = document.querySelector('#csv-import-ajax-nonce').dataset.nonce

        if (!nonce) {
            alert('The nonce is not present on this page');
            return;
        }

        let fields = {formations: []};

        for (let i = 0; i < data.length; i++) {
            fields.formations[i] = data[i].data
        }

        // eslint-disable-next-line no-undef
        let form_data = new URLSearchParams();

        form_data.append('action', 'create_formation_post');
        form_data.append('_wpnonce', nonce);
        form_data.append('data', JSON.stringify(fields));

        //Ajax call to create_formation_post function
        axios.post(wpr_object.ajax_url, form_data)
            .then((response) => {
                setPosted(true)
                setLoading(false)
                console.log(response)
            })
            .catch((error) => {
                alert(error)
            });
    }

    // Markup of CSV button
    return (
        <>
            <h2>Ajouter un fichier</h2>

            <CSVReader onDrop={handleOnDrop} onError={handleOnError} addRemoveButton config={{header: true}}>
                <span>Glissez un CSV ici ou cliquez pour ouvrir votre fichier.</span>
            </CSVReader>

            {/*Conditionnal rendering of import cta if the CSV and data is here*/}
            {data && <Button className={"button-primary"} onClick={() => {post(data); setLoading(true)}}>
                Importer les données
            </Button>}

            {/*Here is the informations on the download an upload of the data in database, based on ajax process time*/}
            {loading && <div className="loaderGenerate"><p style={{color: "red"}}>Chargement en cours, veuillez patienter</p><div className="lds-dual-ring"/></div>}
            {posted && <strong style={msgValidation}>Les données ont été importés avec succès ! ✅</strong>}
        </>
    )
};

export default CSVButton

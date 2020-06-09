// respObj imported from php
//   respIds responses cDefinitions cTitles sContent sTitle

const { Component } = wp.element;
//import './judgmentapp.scss';
import PresentContext from './PresentContext';
import PresentResp from './PresentResp';
import Rationale from './Rationale';
import Confirm from './Confirm';
import ShowEnd from './ShowEnd';
import ShowReview from './ShowReview';
import Mismatches from './Mismatches';
import JudgmentBox from './JudgmentBox';

const review = respObj.review == '1';
const nTrials = respObj.respIds.length;
const codes = [];
const numCodes = respObj.numCodes;
for(let i=1;i<=numCodes;i++) {
    codes[i] = respObj.codeLabels[i];
}

class JudgmentApp extends Component {
    // Tracks various attributes and their changes as the user moves through the trials
    startDate = Date.now();  // UNIX time on page load
    state = {
        trial: 1,   // Each judgment is one trial
        respId: respObj.respIds[0],   // The ID of the Response being judged
        startTime: Math.floor(this.startDate / 1000),    // UNIX time on page load, in seconds
        allDone: false, // Whether the 'ShowEnd' component should be displayed
        showMatches: review,  // display total number matches
    };
    // Labels for Response judgments
    levelTitles = {
        1: "Less Skilled",
        2: "Proficient",
        3: "Master"
    };
    
    /**
     * handleNext: checks whether the user is finished with the current set, saves the current line to
     *      the database, and sets the new startTime
     * Parameters: none
     * Fires: when the user clicks the 'Next' button
     */
    handleNext = (excerpts,codes) => {
        console.log(this.state.respId)

        // Check whether the user has finished all the trials
        if (this.state.trial < nTrials) {
            console.log(this.state.trial)
            this.setState((prevState) => ({
                trial: prevState.trial + 1
            }),
            this.getCase
            );
        } else {
            this.setState(() => ({
                allDone: true
            }));
        }

        console.log(this.state.respId)

        const endDate = Date.now();
        const endTime = Math.floor(endDate / 1000);
        const judgTime = endTime - this.state.startTime;

        let codesArray = []
        let i=0;
        for(i;i<numCodes;i++) {
            codesArray[i+1] = [codes[i],excerpts[i]]
        }

        var dataObj = {
            sub_num: respObj.subNums[this.state.trial-1],
            comp_num: respObj.compNum,
            task_num: respObj.taskNum,
            resp_id: this.state.respId,
            judg_type: review ? 'rev' : 'ind',
            judg_time: judgTime,
            codes: codesArray
        };

        // Save to DB
        this.saveData(dataObj);
        // Check if there's anything in localStorage - if yes, try to push to DB
        if(localStorage.length != 0) {
            var keys = Object.keys(localStorage);
            keys.forEach((key) => {
                if(localStorage.getItem(key)!=null && localStorage.getItem(key)!=undefined && localStorage.getItem(key)!="") {
                    var localObj = JSON.parse(localStorage.getItem(key));
                    localObj._ajax_nonce = respObj.nonce;
                    // Save to DB
                    this.saveData(localObj,key); 
                } else {
                    console.log(typeof key);
                }   
            } );
        }

        // Set new start time
        const newStartDate = Date.now();
        const newStartTime = Math.floor(newStartDate / 1000);
        this.setState(() => ({startTime: newStartTime}));
    }
    
    /**
     * getCase: gets the new Response ID
     * Parameters: none
     * Fires: inside handleNext
     */
    getCase = () => {
        this.setState(() => ({
            respId: respObj.respIds[this.state.trial - 1]
        }));
    }
    
    /**
     * componentDidUpdate: scrolls the webpage so that 'rationale' is in view
     * Parameters: none
     * Fires: after the component changes
     */
    componentDidUpdate = () => {
        if ( document.getElementById("rationale") ) {
            const elDiv = document.getElementById("rationale");
            elDiv.scrollIntoView();
        }
    }

    saveData = (dataObj,key = null) => {
        dataObj.action = 'arc_save_data';
        dataObj._ajax_nonce = respObj.nonce;

        jQuery.ajax({
            type : 'post',
            dataType: 'json',
            url : respObj.ajax_url,
            data : dataObj,
            error : function( response ) {
                console.log("something went wrong (error case)");
                // save to localStorage
                localStorage.setItem(JSON.stringify(dataObj.resp_id),JSON.stringify(dataObj));
            },
            success : function( response ) {
                if( response.type == 'success') {
                    // && dataObj.sub_num == response.data.sub_num
                    console.log('success!');
                    if(key) {
                        localStorage.removeItem(key);
                    }
                } else {
                    console.log("something went wrong");
                    // save to localStorage
                    localStorage.setItem(JSON.stringify(dataObj.resp_id),JSON.stringify(dataObj));
                }
            }
        });
    }

    /**
     * Determines whether to save the matched cases, lets the user move on to reviewing disagreements.
     */
    saveMatches = (saveBool) => {
        // if the user wants to save, make an ajax request to save the matched cases
        // if(saveBool) {
        //     console.log('sending request...');
        //     respObj.matches.forEach((match) => this.saveData(match));
        // }

        // move on to reviewing disagreements
        this.setState(() => ({
            showMatches: false
        }));
    }

    /**
     * Renders the components for JudgmentApp
     */
    render() {
        return (
            <div>
                { this.state.allDone && <ShowEnd />}
                {(!this.state.allDone && this.state.showMatches) &&
                    <Mismatches
                        disagreements={respObj.disagreements}
                        total={respObj.total}
                        saveMatches={this.saveMatches}
                    />
                }
                {(!this.state.allDone && !this.state.showMatches) &&
                    <PresentContext 
                        scenario={respObj.sContent}
                        competencies={respObj.cDefinitions}
                        levelTitles={this.levelTitles}
                        sTitle={respObj.sTitle}
                        cTitle={respObj.cTitles[0]}
                    />
                }
                { (!this.state.allDone && !this.state.showMatches) &&
                    <JudgmentBox
                        respId={ this.state.respId }
                        response={ respObj.responses[this.state.respId] }
                        codes={codes}
                        handleNext={this.handleNext}
                    />
                }
            </div>
        );
    }
}

export default JudgmentApp;
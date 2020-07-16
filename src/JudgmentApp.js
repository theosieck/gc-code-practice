// respObj imported from php
//   respIds responses cDefinitions cTitles sContent sTitle

const { Component } = wp.element;
//import './judgmentapp.scss';
import PresentContext from './PresentContext';
import ShowEnd from './ShowEnd';
import ReviewBox from './ReviewBox';
import JudgmentBox from './JudgmentBox';

const review = respObj.review == '1';
const nTrials = respObj.respIds.length;
let codes = [];
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
        allDone: false // Whether the 'ShowEnd' component should be displayed
        // showMatches: review,  // display total number matches
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
    handleNext = (excerpts,codes,comment) => {
        // Check whether the user has finished all the trials
        if (this.state.trial < nTrials) {
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

        const endDate = Date.now();
        const endTime = Math.floor(endDate / 1000);
        const judgTime = endTime - this.state.startTime;

        let codesArray = []
        for(let i=1;i<=numCodes;i++) {
            codesArray[i] = [codes[i],excerpts[i]]
        }

        var dataObj = {
            sub_num: respObj.subNums[this.state.trial-1],
            comp_num: respObj.compNum,
            task_num: respObj.taskNum,
            resp_id: this.state.respId,
            judg_type: review ? 'rev' : 'ind',
            judg_time: judgTime,
            codes: codesArray,
            judges: respObj.judges,
            code_scheme: respObj.codeScheme,
            comment
        };
        // console.log(dataObj)

        // Save to DB
        this.saveData(dataObj);
        // // Check if there's anything in localStorage - if yes, try to push to DB
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
     * Saves the given dataObj to the database
     */
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
                if( response.type == 'success' && dataObj.sub_num == response.data.sub_num) {
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
     * Renders the components for JudgmentApp
     */
    render() {
        return (
            <div>
                { this.state.allDone && <ShowEnd />}
                {!this.state.allDone &&
                    <PresentContext
                        scenario={respObj.sContent}
                        competencies={respObj.cDefinitions}
                        levelTitles={this.levelTitles}
                        sTitle={respObj.sTitle}
                        cTitle={respObj.cTitles[0]}
                    />
                }
                { (!this.state.allDone && !review) &&
                    <JudgmentBox
                        respId={ this.state.respId }
                        response={ respObj.responses[this.state.respId] }
                        codes={codes}
                        handleNext={this.handleNext}
												resultsObj={respObj.resultsObj}
                    />
                }
                { (!this.state.allDone && review) &&
                    <ReviewBox
                        respId={ this.state.respId }
                        response={ respObj.responses[this.state.respId] }
                        codes={codes}
                        handleNext={this.handleNext}
                        reviewSet={respObj.reviewSet[respObj.subNums[this.state.trial-1]]}
                        matches={respObj.matches[respObj.subNums[this.state.trial-1]]}
                        judge1Comments={respObj.judge1Comments[this.state.respId]}
                        judge2Comments={respObj.judge2Comments[this.state.respId]}
                    />
                }
            </div>
        );
    }
}

export default JudgmentApp;

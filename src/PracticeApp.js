// expObj imported from php
//   expIds exemplars cDefinitions cTitles sContent sTitle

const { Component } = wp.element;
//import './judgmentapp.scss';
import PresentContext from './PresentContext';
import ShowEnd from './ShowEnd';
import PracticeBox from './PracticeBox';

const review = expObj.review == '1';
const nTrials = expObj.expIds.length;
let codes = [];
const numCodes = expObj.numCodes;
for(let i=1;i<=numCodes;i++) {
    codes[i] = expObj.codeLabels[i];
}

class PracticeApp extends Component {
    // Tracks various attributes and their changes as the user moves through the trials
    startDate = Date.now();  // UNIX time on page load
    state = {
        trial: 1,   // Each judgment is one trial
        expId: expObj.expIds[0],   // The ID of the Exemplar being judged
        startTime: Math.floor(this.startDate / 1000),    // UNIX time on page load, in seconds
        allDone: false, // Whether the 'ShowEnd' component should be displayed
				numCorrect: 0,
				totalMatches: 0
    };

    /**
     * handleNext: checks whether the user is finished with the current set, saves the current line to
     *      the database, and sets the new startTime
     * Parameters: none
     * Fires: when the user clicks the 'Next' button
     */
    handleNext = (excerpts,codes,correctCodes,missedCodes,falsePositives) => {
			const endDate = Date.now();
			const endTime = Math.floor(endDate / 1000);
			const judgTime = endTime - this.state.startTime;

			let codesArray = []
			let correctCodesArray = []
			for(let i=1;i<=numCodes;i++) {
					codesArray[i] = [codes[i],excerpts[i]]
					if(correctCodes.includes(i)) {
						correctCodesArray.push(i)
					}
			}

			this.setState((prevState) => ({
				numCorrect:prevState.numCorrect + correctCodesArray.length,
				totalMatches:prevState.totalMatches + correctCodesArray.length + missedCodes.length + falsePositives.length
			}))

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

        var dataObj = {
            sub_num: expObj.subNums[this.state.trial-1],
            comp_num: expObj.compNum,
            task_num: expObj.taskNum,
            exp_id: this.state.expId,
            judg_time: judgTime,
            codes: codesArray,
            code_scheme: expObj.codeScheme,
						correctCodes:correctCodesArray,
						missedCodes,
						falsePositives
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
                    localObj._ajax_nonce = expObj.nonce;
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
     * getCase: gets the new Exemplar ID
     * Parameters: none
     * Fires: inside handleNext
     */
    getCase = () => {
        this.setState(() => ({
            expId: expObj.expIds[this.state.trial - 1]
        }));
    }

    /**
     * Saves the given dataObj to the database
     */
    saveData = (dataObj,key = null) => {
        dataObj.action = 'save_prac_data';
        dataObj._ajax_nonce = expObj.nonce;

        jQuery.ajax({
            type : 'post',
            dataType: 'json',
            url : expObj.ajax_url,
            data : dataObj,
            error : function( e ) {
                console.log("something went wrong (error case)");
								console.log(e);
                // save to localStorage
                localStorage.setItem(JSON.stringify(dataObj.exp_id),JSON.stringify(dataObj));
            },
            success : function( response ) {
                if( response.type == 'success' && dataObj.sub_num == response.data.sub_num) {
                    console.log('success!');
                    if(key) {
                        localStorage.removeItem(key);
                    }
                } else {
                    console.log("something went wrong");
										console.log(response);
                    // save to localStorage
                    localStorage.setItem(JSON.stringify(dataObj.exp_id),JSON.stringify(dataObj));
                }
            }
        });
    }

    /**
     * Renders the components for PracticeApp
     */
    render() {
        return (
            <div>
                { this.state.allDone &&
									<ShowEnd
										numCorrect={this.state.numCorrect}
										total={this.state.totalMatches}
									/>
								}
                {!this.state.allDone &&
									<div>
                    <PresentContext
                        scenario={expObj.sContent}
                        competencies={expObj.cDefinitions}
                        sTitle={expObj.sTitle}
                        cTitles={expObj.cTitles}
                    />
										<PracticeBox
                        expId={ this.state.expId }
                        response={ expObj.exemplars[this.state.expId] }
                        codes={codes}
                        handleNext={this.handleNext}
												goldCodes={expObj.goldCodes[this.state.expId]}
                    />
									</div>
                }
            </div>
        );
    }
}

export default PracticeApp;
